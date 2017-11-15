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
-- In this function, first we will try to submit data from recent application and
-- then we will try to post other call related details (cdr data)
function application_Hangup(s, status, arg)
  -- in case call is still active
  if oCall:ready() then
    oCall:hangup()
  end

  call_status = 'hangup'

  oFreeswitch.consoleLog("INFO", string.format("[ spool_id=%s ]\n", spool_id))
  oFreeswitch.consoleLog("INFO", "call hangup: " .. tostring(oCall:getVariable("hangup_cause")))

  local hangupApplicationResult = {}
  hangupApplicationResult['time_start']   = tostring(oCall:getVariable("start_epoch"))
  hangupApplicationResult['time_connect'] = tostring(oCall:getVariable("answer_epoch"))
  hangupApplicationResult['time_end']     = tostring(oCall:getVariable("end_epoch"))
  hangupApplicationResult['amount']       = tostring(oCall:getVariable("duration"))
  hangupApplicationResult['amount_net']   = tostring(oCall:getVariable("billsec"))
  hangupApplicationResult['status']       = "completed"
  hangupApplicationResult['response']     = ''

  if (hangupApplicationResult['amount'] == nil or hangupApplicationResult['amount'] == '') then
    hangupApplicationResult['amount']     = 0
    hangupApplicationResult['amount_net'] = 0
  end

  -- only update in case of failure
  local response_str = oCall:hangupCause()
  if oCall:answered() == false then
    hangupApplicationResult['result']   = 'error'
    hangupApplicationResult['status']   = 'failed'
    hangupApplicationResult['response'] = oCall:hangupCause()
  else
    hangupApplicationResult['result']   = 'success'
  end

  -- USER AND NETWORK INFO
  oFreeswitch.consoleLog("INFO", "CallerID:" .. " - " .. tostring(oCall:getVariable("Caller-Caller-ID-Number")))
  oFreeswitch.consoleLog("INFO", "CallerID Name:" .. " - " .. tostring(oCall:getVariable("Caller-Caller-ID-Name")))
  oFreeswitch.consoleLog("INFO", "Network Addr:" .. " - " .. tostring(oCall:getVariable("Caller-Network-Addr")))
  oFreeswitch.consoleLog("INFO", "Destination:" .. " - " .. tostring(oCall:getVariable("Caller-Destination-Number")))
  oFreeswitch.consoleLog("INFO", "Status:" .. " - " .. tostring(oCall:getVariable("state")))
  oFreeswitch.consoleLog("INFO", "Dialplan:" .. " - " .. tostring(oCall:getVariable("dialplan")))
  oFreeswitch.consoleLog("INFO", "Call Result:" .. " - " .. status)

  -- collect data from recent application
  app_result = {}
  app_result['extra'] = {} -- also send call end notification to server
  if aOutput ~= nil then
    for var_name_app, var_name_gateway in pairs(aOutput) do
      app_result[var_name_app] = tostring(oCall:getVariable(var_name_gateway))
    end
  end

  -- if hangup application is set then also include hangup application results as extra
  if hangupApplicationID ~= nil then
    if app_id ~= nil then
      -- collect data for hangup application
      extra_hangup_request = ictcore_access   -- include default parameters
      extra_hangup_request['spool_id']         = spool_id
      extra_hangup_request['application_id']   = hangupApplicationID
      extra_hangup_request['application_data'] = hangupApplicationResult
      -- in the array of extra data, hangup application is one element
      extra = {}
      extra['hangup']     = extra_hangup_request
      app_result['extra'] = extra
    else
      app_id     = hangupApplicationID
      app_result = hangupApplicationResult
    end
  end

  -- finally submit data
  application_fetch()

  oFreeswitch.consoleLog("INFO", "All Done")
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
        end
        oCall:setVariable(var_name, var_value)
      end
    end

    if aBatch ~= nil then
      for cmd_id, aCommand in pairs(aBatch) do
        oCall:execute(aCommand.name, aCommand.data)
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
  hangupApplicationID = oCall:getVariable('disconnect_application_id')
end

oCall:setVariable('api_hangup_hook', '')  -- cancel default hangup hook
oCall:setHangupHook("application_Hangup") -- set hangup to a function

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
