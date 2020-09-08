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

-- ====================================
-- Program start here
-- ====================================

-- Basic objects and variables
-- ---------------------------
oFreeswitch = freeswitch
oFreeAPI    = freeswitch.API() -- Freeswitch API interface
call_status = 'hangup'         -- call status

spool_id   = argv[1]  -- id to corresponding call record in ictcore
app_id     = argv[2]  -- id of currently running application, name ?
result_val = argv[3]  -- application status after executing it
response   = argv[4]  -- application output after executing it

app_result = {}       -- save temp result into result array, so it be used later with app_fetch
app_result['result']  = result_val
app_result['response']= response
app_result['call_id'] = tostring(env:getHeader("uuid"))

app_result['time_start']   = tostring(env:getHeader("start_epoch"))
app_result['time_connect'] = tostring(env:getHeader("answer_epoch"))
app_result['time_end']     = tostring(env:getHeader("end_epoch"))
app_result['amount']       = tostring(env:getHeader("billsec"))
app_result['status']       = 'completed'
app_result['response']     = ''

if (app_result['amount'] == nil or app_result['amount'] == '') then
  app_result['amount']     = 0
  app_result['amount_net'] = 0
end

-- determine call status
local time_connect = tonumber(env:getHeader("answer_epoch"))
if (app_result['result'] == 'error' or time_connect == nil or time_connect < 1) then
  app_result['status']   = 'failed'
  if (app_result['response'] == '') then
    app_result['response'] = tostring(env:getHeader("hangup_cause"))
  end
end

-- Logging
-- -------
freeswitch.consoleLog("INFO", string.format("[ spool_id=%s ]\n", spool_id))
freeswitch.consoleLog("INFO", "call hangup: " .. tostring(env:getHeader("hangup_cause")))

-- USER AND NETWORK INFO
freeswitch.consoleLog("INFO", "CallerID:" .. " - " .. tostring(env:getHeader("Caller-Caller-ID-Number")))
freeswitch.consoleLog("INFO", "CallerID Name:" .. " - " .. tostring(env:getHeader("Caller-Caller-ID-Name")))
freeswitch.consoleLog("INFO", "Network Addr:" .. " - " .. tostring(env:getHeader("Caller-Network-Addr")))
freeswitch.consoleLog("INFO", "Destination:" .. " - " .. tostring(env:getHeader("Caller-Destination-Number")))
freeswitch.consoleLog("INFO", "Status:" .. " - " .. tostring(env:getHeader("Answer-State")))
freeswitch.consoleLog("INFO", "Direction:" .. " - " .. tostring(env:getHeader("Caller-Direction")))
freeswitch.consoleLog("INFO", "Call Result:" .. " - " .. app_result['status'])
freeswitch.consoleLog("INFO", "Response:" .. " - " .. app_result['response'])

-- finally submit data
application_fetch()

-- See everything
-- dat = env:serialize()
-- freeswitch.consoleLog("INFO","Here's everything:\n" .. dat .. "\n")

freeswitch.consoleLog("INFO", "All Done")
