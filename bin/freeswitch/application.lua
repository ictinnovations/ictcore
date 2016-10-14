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

  local startAppResult = {}
  startAppResult['time_start']   = tostring(oCall:getVariable("start_epoch"))
  startAppResult['time_connect'] = tostring(oCall:getVariable("answer_epoch"))
  startAppResult['time_end']     = tostring(oCall:getVariable("end_epoch"))
  startAppResult['amount']       = tostring(oCall:getVariable("duration"))
  startAppResult['amount_net']   = tostring(oCall:getVariable("billsec"))
  startAppResult['status']       = "completed"
  startAppResult['response']     = ''

  if (startAppResult['amount'] == nil or startAppResult['amount'] == '') then
    startAppResult['amount']     = 0
    startAppResult['amount_net'] = 0
  end

  -- only update in case of failure
  local response_str = oCall:hangupCause()
  if oCall:answered() == false then
    startAppResult['status']   = 'failed'
    startAppResult['response'] = oCall:hangupCause()
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

  -- if current application is not starting application then also include 
  -- starting application in results as extra
  if startAppId ~= app_id then
    -- before submitting also add start app final update as extra data
    -- collect data for start app
    extra_start_request = ictcore_access   -- include default parameters
    extra_start_request['spool_id']         = spool_id
    extra_start_request['application_id']   = startAppId
    extra_start_request['application_data'] = startAppResult
    -- in the array of extra data, start application is one element
    extra = {}
    extra['start']      = extra_start_request
    app_result['extra'] = extra
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

  api_request['spool_id']         = spool_id
  api_request['application_id']   = app_id
  api_request['application_data'] = JSON:encode(app_result)

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
        elseif var_name == 'start_app_id' then
          startAppId = var_value
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
end

startAppId = app_id   -- probably app_id for originate or inbound application, we are saving it for future use

oCall:setVariable('api_hangup_hook', '')  -- cancel default hangup hook
oCall:setHangupHook("application_Hangup") -- set hangup to a function

-- Main loop: Dialplan / application will be processed in this loop
-- ----------------------------------------------------------------
while oCall:ready() do
  call_status = 'active'

  -- fetch next instructions from ictcore remote Gateway interface
  local appDataAll = application_fetch()
  if (appDataAll == false or appDataAll == '') then
    oCall:hangup()
    break
  else -- we are ready to execute new applications
    app_count = 0
    for appId, appData in pairs(appDataAll) do
      application_execute(appData)
      app_count = app_count + 1
    end
    if app_count == 0 then
      oCall:hangup()  -- no new application executed, it mean we are at the end, so hangup
      break
    end
  end
end

oCall:hangup()
