<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

 use ICT\Core\DB;
use ICT\Core\Api;
use ICT\Core\User;
use ICT\Core\Tenant;
use Firebase\JWT\JWT;
use ICT\Core\Corelog;
use ICT\Core\CoreException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use ICT\Core\Conf;
use ICT\Core\Gateway\Sendmail;

class Forgot_password
{

  protected static $table = 'forgot_password';
  protected static $primary_key = 'id';

  protected static $fields = array(
    'usr_id',
    'email',
    'token',
    'created_at',
    );
  
  
    public function forgot($data)
  {
    $email = $data['email'];
    $table_usr = 'usr';
    $query = "SELECT * FROM " . $table_usr . " WHERE email = '" . $email . "'";
    $result = DB::query($table_usr, $query, true);
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[] = $row;
      foreach ($rows as $value) {
        $aValue = $value;
      }
      if (!empty($aValue)) {
        $token = $this->generate_token($email);
        $usr_id = $aValue['usr_id'];
        $data = [
          'usr_id' => $usr_id,
          'email' => $email,
          'token' => $token,
          'created_at' => time() + 600
        ];
        // $table_name = 'forgot_password';
        $result = DB::update(self::$table, $data, false);
        // $link = "https://demo.ictfax.com/;
        // $link = Conf::get('website:url', 'default');
        // $link .= "reset-password?code=" . $token;
        $link = "http://localhost:4200/#/auth/reset-password?code=". $token; 
     
        $templateFile = 'Program/Emailtofax/data/email_pass.tpl.php';
        $templateBody = include $templateFile;
        $body = str_replace(['[link]'], $link, $templateBody);
        $body = str_replace('[first_name]', $aValue['first_name'], $body);
        $route = Sendmail::default_route();
        $transport = (new EsmtpTransport($route->host, (int) $route->port))
          ->setUsername($route->username)
          ->setPassword($route->password);
        // Create the Mailer using created Transport
        $mailer = new Mailer($transport);
        // Create a message
        $message = (new Email())
          ->subject('Forgot Password Request')
          ->from(new Address('kashif@ictinnovations.com', 'ICT Fax'))
          ->to($email)
          ->html($body);
        // Symfony's send() returns void and signals failure by throwing, unlike
        // Swift which returned a recipient count. Testing the return value here
        // would report every successful send as a failure.
        try {
          $mailer->send($message);
        } catch (TransportExceptionInterface $send_error) {
          Corelog::log('email send failed: ' . $send_error->getMessage(), Corelog::ERROR);
          throw new CoreException(500, 'Error while sending mail');
        }
        return true;
      } else {
        throw new CoreException(401, 'The provided email is not registered in our system. Please enter a valid email address.');
      }
    }
  }
  
  
  public function reset_password($data)
  {
    // Corelog::log('email ' . print_r($data, true), Corelog::INFO);
    $query = "SELECT * FROM " . self::$table . " WHERE token = '" . $data['code'] . "'";
    $result = DB::query(self::$table, $query);
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[] = $row;
    }
    foreach ($rows as $singleRow) {
      $aRow = $singleRow;
    }
    if (!empty($aRow)) {
      $token = $this->decode_token($aRow['token']);
      if ($token !== false) {
        if (!empty($data['password'])) {
          $password_hash = md5($data['password']);
          $usr_table = 'usr';
          $usr_query = "UPDATE " . $usr_table . " SET passwd = '" . $password_hash . "' WHERE usr_id = '" . $aRow['usr_id'] . "'";
          $usr_result = DB::query($usr_table, $usr_query);
          if ($usr_result) {
            $del_query = "DELETE FROM " . self::$table . " WHERE usr_id = '" . $aRow['usr_id'] . "' AND token = '" . $data['code'] . "'";
            $del_result = DB::query(self::$table, $del_query, false);
            return $del_result;
          } else {
            throw new CoreException(417, 'Password reset failed.');
          }
        } else {
          throw new CoreException(400, 'Password field is empty.');
        }
      } else {
        throw new CoreException(401, 'Token has expired.');
      }
    } else {
      throw new CoreException(422, 'Invalid Token.');
    }
  }
  
  
  public function generate_token($email = null)
  {
    $key_file = Conf::get('security:private_key', '/usr/ictcore/etc/ssh/ib_node');
    $private_key = file_get_contents($key_file);
    $token = array(
      "email" => $email,
      "exp" => time() + 600
    );
    return JWT::encode($token, $private_key, Conf::get('security:hash_type', 'RS256'));
  }
  
  
  public function decode_token($token)
  {
    try {
      $key_file = Conf::get('security:public_key', '/usr/ictcore/etc/ssh/ib_node.pub');
      $hash_type = Conf::get('security:hash_type', 'RS256');
      $public_key = file_get_contents($key_file);
      return JWT::decode($token, new Key($public_key, $hash_type));
    } catch (ExpiredException $e) {
      // Handle token expiration
      return false;
    } catch (SignatureInvalidException $e) {
      // Handle signature validation failure
      return false;
    }
  }
}
