<?php

require_once("main.php");

if (!FRAME_KEY || !in_array(FRAME_KEY, APP_KEY) || !PUSH_MSG_CONTENT)
  return;

$appManager = new app();

$appManager->appSend(PUSH_MSG_ROBOT, PUSH_MSG_TYPE, PUSH_MSG_SOURCE, PUSH_MSG_SENDER, PUSH_MSG_CONTENT, PUSH_MSG_EXT);

echo json_encode(
  array(
    "data" => NULL,
    "status" => array(
      "code" => 200,
      "msg" => "ok"
    ),
    "usedtime" => 0
  )
);