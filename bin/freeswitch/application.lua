--[[
application.lua
Disclaimer: Use at your own risk.  No implied warranties or help if/when stuff blows up.
]]

CONF = (loadfile "/usr/ictcore/bin/freeswitch/lib/LIP.lua")() -- one-time load of INI files related routines

local aConf = CONF.load('/etc/ictcore.conf');
ictcore_url    = tostring(aConf.gatewayhub.url)
ictcore_access = {
  username      = tostring(aConf.gatewayhub.username),
  password      = tostring(aConf.gatewayhub.password),
  gateway_flag  = 8
}

JSON = (loadfile "/usr/ictcore/bin/freeswitch/lib/JSON.lua")() -- one-time load of JSON related routines
UrlEncode = (loadfile "/usr/ictcore/bin/freeswitch/lib/url_encode.lua")() -- one-time load of url encoder library

-- Hangup_Hook What to do after hangup
-- -----------------------------------
-- In this function, we will try to submit data from recent application and
function application_Hangup(s, status, arg)
  call_status = 'hangup'

  oFreeswitch.consoleLog("INFO", string.format("[ spool_id=%s ]\n", spool_id))
  oFreeswitch.consoleLog("INFO", "call hangup: " .. tostring(oCall:getVariable("hangup_cause")))

  -- collect data from recent application
  app_result = {}
  if aOutput ~= nil then
    for var_name_app, var_name_gateway in pairs(aOutput) do
      app_result[var_name_app] = tostring(oCall:getVariable(var_name_gateway))
    end
  end

  -- if application is still exist then post its result to ictcore
  if (app_id ~= nil and app_id ~= hangupApplicationID) then
    application_fetch()
  end

  oFreeswitch.consoleLog("INFO", "Execution Done")
end

-- Application Fetch
-- ---------------------------
-- This function will download new instructions from ICTCore gateway interface, after submitting current status
-- this function has three main things to do
-- 1. collect authentication, application output, call id and encode it
-- 2. post request and get response
-- 3. decode response, to make it ready for further use
function application_fetch()
  local api_request  = ictcore_access
  local api_response = ''

  -- application_data = JSON:encode must be placed before application_id, And I don't know why ?
  api_request['application_data'] = JSON:encode(app_result)
  api_request['spool_id']         = spool_id
  api_request['application_id']   = app_id

  -- disable any further execution, untill we have fresh application id
  app_id = nil

  if call_status == 'active' then
    oCall:execute("curl", ictcore_url .. " post " .. UrlEncode.table(api_request))
    local api_response_code = oCall:getVariable("curl_response_code")
    if api_response_code == '200' then
      api_response = oCall:getVariable("curl_response_data")
    else
      api_response = false
    end
    oFreeswitch.consoleLog("INFO", tostring(api_response_code))
    oFreeswitch.consoleLog("INFO", tostring(api_response))
  elseif call_status == 'hangup' then
    api_response = oFreeAPI:executeString("curl " .. ictcore_url .. " post " .. UrlEncode.table(api_request))
  else
  end

  if api_response ~= false then
    return JSON:decode(api_response)
  else
    return false
  end
end

-- Application Execute
-- ---------------------------
-- This function will be responsible to execute given dialplan / application, one application at a time
-- this function has three main things to do with Freeswitch
-- 1. set all required variables
-- 2. execute each step in application
-- 3. collect output variables
function application_execute(appData)
  app_id  = appData.application_id
  aBatch  = appData.batch
  aInput  = appData.input
  aOutput = appData.output

  if call_status == 'active' then
    if aInput ~= nil then
      for var_name, var_value in pairs(aInput) do
        if var_name == 'spool_id' then
          spool_id = var_value
        end
        if var_name == 'disconnect_application_id' then
          hangupApplicationID = var_value
          oCall:setVariable('api_hangup_hook', 'lua /usr/ictcore/bin/freeswitch/spool_failed.lua ' .. spool_id .. ' ' .. hangupApplicationID .. ' success')
        end
        oCall:setVariable(var_name, var_value)
      end
    end

    if aBatch ~= nil then
      for cmd_id, aCommand in pairs(aBatch) do
        if application_execute_internal(aCommand) == false then
          oCall:execute(aCommand.name, aCommand.data)
        end
      end
    end

    app_result = {}
    if aOutput ~= nil then
      for var_name_app, var_name_gateway in pairs(aOutput) do
        app_result[var_name_app] = oCall:getVariable(var_name_gateway)
      end
    end

  elseif call_status == 'hangup' then
    -- in case of hangup we have no session data and can only execute API command
    for cmd_id, aCommand in pairs(aBatch) do
      oFreeAPI:executeString(aCommand.name .. ' ' .. aCommand.data)
    end

  else -- call status else
    -- nothing to do, unaccepted scenario
  end

end

function application_execute_internal(appData)
  if appData.name == 'transfer' then
    transferData = appData.data:gmatch("[^%s]+")
    transferExtension = transferData()
    transferSource = transferData()
    transferContext = transferData()
    oCall:transfer(transferExtension, transferSource, transferContext)
    oCall:destroy() -- this statement can stop script execution, it will produce an error, ignore it
    -- TODO: replace above "destroy" with return, i.e return to main and then return again to exit from script
  end

  return false
end

-- ====================================
-- Program start here
-- ====================================

-- Basic objects and variables
-- ---------------------------
oFreeswitch = freeswitch
oFreeAPI    = freeswitch.API() -- Freeswitch API interface
oCall       = session          -- call object i.e channel/leg session
call_status = 'unknown'        -- call status

spool_id   = argv[1]  -- id to corresponding call record in ictcore
app_id     = argv[2]  -- id of currently running application, name ?
result_val = argv[3]  -- application output after executing it

-- Some validation
if spool_id == nil then
  oCall:hangup()
end
if app_id == nil then -- we can't continue without proper application
  oCall:hangup()
end

app_result = {}       -- save temp result into result array, so it can be used later with app_fetch
app_result['result'] = result_val
-- if app is inbound request, then we should prepare result according to Inbound app
if app_id == 'inbound' then
  app_result['destination'] = oCall:getVariable('destination_number')
  app_result['source']      = oCall:getVariable('caller_id_number')
  app_result['context']     = 'external' -- TODO oCall:getVariable('context')
else -- probably it is an originate request so try to read disconnect_application_id
-- replace default hangup hook
  hangupApplicationID = oCall:getVariable('disconnect_application_id')
  oCall:setVariable('api_hangup_hook', 'lua /usr/ictcore/bin/freeswitch/spool_failed.lua ' .. spool_id .. ' ' .. hangupApplicationID .. ' success')
end

-- also add hangup function
oCall:setVariable('session_in_hangup_hook', 'true') -- make sure session is available in hangup function
oCall:setHangupHook("application_Hangup") -- set hangup to a function
-- oCall:setAutoHangup(false)                -- continue in the dialplan

-- Main loop: Dialplan / application will be processed in this loop
-- ----------------------------------------------------------------
while oCall:ready() do
  call_status = 'active'

  -- fetch next instructions from ictcore remote Gateway interface
  local newAppData = application_fetch()
  if (newAppData == false or newAppData == '') then
    oCall:hangup() -- no new application to executed, it mean we are at the end, so hangup
    break
  else -- we are ready to execute new application
    application_execute(newAppData)
  end
end

oCall:hangup()
