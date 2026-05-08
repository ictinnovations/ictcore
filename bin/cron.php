<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Service;
use ICT\Core\Task;
use ICT\Core\Corelog;
use ICT\Core\User;
use ICT\Core\Tenant;
use ICT\Core\Transmission;
use ICT\Core\Message\Document;
use ICT\Core\Spool;
use ICT\Core\Password_Policy;
use ICT\Core\DB;

// default include is /usr/ictcore/core
chdir(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core');

include_once "Core.php";

function cron_process()
{
    // -----------------------------------------------
    // OLD ORIGINAL FUNCTIONS (UNTOUCHED)
    // -----------------------------------------------

    // process all pending retries
    Task::process_all();

    // counting user password expiration
    $Password_Policy = new Password_Policy();
    $Password_Policy->password_exp_limit();

    // reload method for all services
    $listService = Service::load_all();
    foreach ($listService as $oService) {
        $oService->config_update();
    }

    // execute email fetch script
    include_once('../bin/sendmail/gateway.php');

    date_default_timezone_set('UTC');
    $newDateTime = date('h:i A');
    if ($newDateTime == '12:00 AM') {

        Corelog::log('Reseting tenant limits at: ' . date('r'), Corelog::INFO);

        $listUser = User::search();
        foreach ($listUser as $aUser) {
            $oUser = new User($aUser['user_id']);
            $oUser->reset_daily_sent();
            if (date('d') == 1) {
                $oUser->reset_monthly_sent();
            }
        }
    }

    // -----------------------------------------------
    // ⭐ ADDED: schedule.fax.cron.php functions
    // -----------------------------------------------

    Corelog::log("=== FAX CRON STARTED ===", Corelog::INFO);
    Corelog::log("TIME: " . date('Y-m-d H:i:s'), Corelog::INFO);

    $currentTime = time();

    $query = "
        SELECT transmission_id
        FROM transmission
        WHERE status = '" . Transmission::STATUS_PENDING . "'
        AND (schedule_time IS NULL OR schedule_time <= $currentTime)
        ORDER BY transmission_id DESC
        LIMIT 1
    ";

    $results = DB::query('transmission', $query);

    if (empty($results)) {
        Corelog::log("No pending transmissions at " . date('Y-m-d H:i:s'), Corelog::INFO);
        return;
    }

    foreach ($results as $row) {
        try {
            $transmission = new Transmission($row['transmission_id']);

            if (!isset($transmission->transmission_id)) {
                Corelog::log("Invalid transmission object — skipping", Corelog::WARNING);
                continue;
            }

            if (empty($transmission->contact_id)) {
                Corelog::log("Contact missing — calling send() anyway", Corelog::WARNING);
            }

            $success = $transmission->send();

            // if ($success) {
            //     $transmission->status = Transmission::STATUS_COMPLETED;
            //     $transmission->response = "Sent successfully";
            // } else {
            //     $transmission->status = Transmission::STATUS_FAILED;
            //     $transmission->response = "Send failed";
            // }

            $transmission->last_run = time();

            if (method_exists($transmission, 'save')) {
                $transmission->save();
            } else {
                Corelog::log("Save method missing — cannot update", Corelog::WARNING);
            }

        } catch (Exception $e) {
            Corelog::log("Error: " . $e->getMessage(), Corelog::ERROR);
        }
    }

    Corelog::log("=== FAX CRON FINISHED ===", Corelog::INFO);
}

// run cron
cron_process();
exit();
