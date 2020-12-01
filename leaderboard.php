<html lang="en-us">
<head>
  <meta charset="utf-8"/>
  <link href='//fonts.googleapis.com/css?family=Source+Code+Pro:300&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
  <style>
    body {
      background-color: #0f0f23;
      color: #cccccc;
      font-family: "Source Code Pro", monospace;
      font-size: 14pt;
    }
    .star {
      font-size: 18pt;
      line-height: 80%;
    }
    .star-big {
      font-size: 20pt;
    }
    .star-gold {
      color: #ffff66;
      text-shadow: 0 0 5px #ffff66;
    }
    .star-silver {
      color: #666699;
    }
    .star-gray {
      color: #333333;
    }
    #main-table {
      border-collapse: collapse;
    }
    #main-table td, th {
      border: 1px solid rgb(51, 51, 51);
      padding-left: 5px;
      padding-right: 5px;
      text-align: center;
      font-size: 14pt;
    }
    #main-table td.left-align {
      text-align: left;
    }
    #main-table td.star-table-1 {
      border-right: 0px;
      padding-left: 3px;
      padding-right: 0px;
      text-align: center;
      min-width: 14px;
    }
    #main-table td.star-table-2 {
      border-left: 0px;
      padding-left: 0px;
      padding-right: 3px;
      text-align: center;
      min-width: 14px;
    }
    input {
      border: 1px solid #666666;
      background: #10101a;
      font-family: inherit;
      font-weight: normal;
      color: white;
      font-size: 12pt;
    }
    small {
      font-size: 80%;
      color: #888888;
    }
    .left-align {
      text-align: left;
    }
    .gold {
      color: #ffda59;
      text-shadow: 0 0 5px #ffda59;
      font-weight: bold;
    }
    .silver {
      color: #d3e2ec;
      text-shadow: 0 0 5px #d3e2ec;
      font-weight: bold;
    }
    .bronze {
      color: #e28960;
      text-shadow: 0 0 5px #e28960;
      font-weight: bold;
    }
    a {
      text-decoration: none;
      color: #009900;
    }
    a:hover {
      color: #99ff99;
    }
    a.tooltip {
      text-decoration: none;
      color: #ffff66;
    }
    a.tooltip:hover {
      cursor: crosshair;
      font-size: 20pt;
      position: relative
    }
    a.tooltip > span {
      text-align: left;
      color: white;
      display: none;
      font-weight: normal;
      font-size: 12pt;
    }
    a.tooltip:hover > span {
      border: #aaaaaa 1px solid;
      background-color: black;
      padding: 8px 8px 8px 8px;
      display: block;
      z-index: 100;
      position: absolute;
      left: 5px;
      top: 20px;
      margin: 10px;
      text-decoration: none;
      white-space: nowrap;
    }
    </style>
</head>
<body>

  <div id="content">

  <?php

  /****************************************************************************/

  // Retrieve and decode the board's JSON file
  // Implements caching of the file so as to not retrieve it again from AoC's
  // server if it's been retrieved less than $cache_seconds seconds ago.
  function get_data($board_id, $event_year, $session_id) {

    // Path to the JSON file on the local server
    $JSON_path = "./board_" . $board_id . ".json";

    // Number of seconds to cache the file
    $cache_seconds = 900;

    // The URL of the JSON file on the AoC server
    $remote_url = "https://adventofcode.com/" . $event_year . "/leaderboard/private/view/" . $board_id . ".json";

    // Determine if file needs to be retrieved from the AoC server
    if (!file_exists($JSON_path)) {
      $retrieve = true;
    } else if (file_exists($JSON_path)) {
      $retrieve = ((time() - filemtime($JSON_path)) > $cache_seconds);
    }

    // Retrieve file from the AoC server if nonexistent or cache expired
    if ($retrieve) {

      // Try and retrieve JSON file using cURL
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $remote_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: session=" . $session_id));
      $result = curl_exec($ch);
      $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      // Save to disk if retrieval was successful
      if ($status == "200") {
        $file = fopen($JSON_path, 'w');
        if ($file) {
          fwrite($file, $result);
          fclose($file);
        }
      }

    }

    // Read file from disk
    if (file_exists($JSON_path)) {
      $content = file_get_contents($JSON_path);
      $mtime = filemtime($JSON_path);
    } else {
      return null;
    }

    // Decode JSON
    $data = json_decode($content, true);
    $data["last_update"] = $mtime;

    return $data;

  }

  /****************************************************************************/

  // Format the solve time as a nice string
  function nice_solve_time($puzzle_open, $ts) {
    $open_ts = $puzzle_open->getTimestamp();
    $secs = $ts - $open_ts;
    $result = "";
    $days = 0;
    $hours = 0;
    $minutes = 0;
    $bits = [];
    if ($secs > 86400) {
      $days = intval($secs/86400);
      array_push($bits, $days . "d");
      $secs -= $days * 86400;
    }
    if ($secs > 3600 || count($bits) > 0) {
      $hours = intval($secs/3600);
      array_push($bits, $hours . "h");
      $secs -= $hours * 3600;
    }
    if ($secs > 60 || count($bits) > 0) {
      $minutes = intval($secs/60);
      array_push($bits, $minutes . "m");
      $secs -= $minutes * 60;
    }
    return implode(" ", $bits);
  }


  /****************************************************************************/

  // EDIT THIS FILE TO SET YOUR LEADERBOARD PARAMETERS
  require("config.php");
  $event_year = strval($event_year);

  if (isset($_GET["sort_field"])) {
    $sort_field = $_GET["sort_field"];
  } else {
    $sort_field = "local_score";
  }

  if (!empty($board_id)) {
    $json_fname = $board_id . ".json";
  }

  // Get data -- either from the local server's cache or from AoC
  if (!empty($board_id) && !empty($session_id)) {
    $data = get_data($board_id, $event_year, $session_id);
  } else {
    $data = null;
  }

  /****************************************************************************/
  // OUTPUT PAGE

  if (!empty($data)) { ?>

    <h4>Advent of Code <?= $event_year ?> &mdash; Private leaderboard #<?= $board_id ?> <?php if ($fromGET) { ?><small><a href="<?php echo parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); ?>">view another</a></small><?php } ?></h4>

    <?php

    $players = [];
    foreach ($data["members"] as $player) {
        array_push($players, $player);
    }

    // Determine medals
    for ($i = 0; $i < count($players); $i++) {
      $players[$i]["medals"] = [];
      $players[$i]["gold"] = 0;
      $players[$i]["silver"] = 0;
      $players[$i]["bronze"] = 0;
      $players[$i]["medals_tot"] = 0;
    }
    for ($day = 1; $day <= 25; $day++) {
      for ($i = 0; $i < count($players); $i++) {
        $players[$i]["medals"][$day] = [];
      }
      for ($star_num = 1; $star_num <= 2; $star_num++) {

        for ($i = 0; $i < count($players); $i++) {
           $players[$i]["medals"][$day][$star_num] = [];
        }

        $times = [];
        for ($i = 0; $i < count($players); $i++) {
          $player = $players[$i];
          if (array_key_exists($day, $player["completion_day_level"])
          && array_key_exists($star_num, $player["completion_day_level"][$day])) {
            array_push($times, [$player["id"], $player["completion_day_level"][$day][$star_num]["get_star_ts"]]);
          }
        }
        usort($times, function($a, $b) {
          if ($a[1] < $b[1]) return -1;
          else if ($a[1] > $b[1]) return +1;
          else return 0;
        });

        for ($i = 0; $i < count($players); $i++) {
          if (count($times) >= 1 && $times[0][0] == $players[$i]["id"]) {
            $players[$i]["medals"][$day][$star_num]["gold"] = $times[0][1];
            $players[$i]["gold"] += 1;
          } else if (count($times) >= 2 && $times[1][0] == $players[$i]["id"]) {
            $players[$i]["medals"][$day][$star_num]["silver"] = $times[0][1];
            $players[$i]["silver"] += 1;
          } else if (count($times) >= 3 && $times[2][0] == $players[$i]["id"]) {
            $players[$i]["medals"][$day][$star_num]["bronze"] = $times[0][1];
            $players[$i]["bronze"] += 1;
          }
        }

      }
    }
    for ($i = 0; $i < count($players); $i++) {
      $players[$i]["medals_tot"] = $players[$i]["gold"] + $players[$i]["silver"] + $players[$i]["bronze"];
    }

    // Sort players by local score
    function comparator($a, $b) {
      global $sort_field;
      if ($a[$sort_field] < $b[$sort_field]) {
        return +1;
      } else if ($a[$sort_field] > $b[$sort_field]) {
        return -1;
      } else {
        return 0;
      }
    };
    usort($players, "comparator");

    $medals_tot_img = '<img src="medals.png"/>';
    $medal_gold_img = '<img src="medal_gold.png"/>';
    $medal_silver_img = '<img src="medal_silver.png"/>';
    $medal_bronze_img = '<img src="medal_bronze.png"/>';

    ?>

    <table id="main-table">

      <tr>
        <th><strong>#</strong></th>
        <th class="left-align"><strong>Name</strong></th>
        <th><strong>Score</strong></th>
        <th><span class="star-big star-gold">*</span></th>
        <th><?= $medal_gold_img ?></th>
        <th><?= $medal_silver_img ?></th>
        <th><?= $medal_bronze_img ?></th>
        <th><?= $medals_tot_img ?></th>
        <!--<th><strong>GScore</strong></th>-->
        <?php for ($day = 1; $day <= 25; $day++) { ?>
          <th colspan="2"><?= $day ?></th>
        <?php } ?>
      </tr>

      <?php for ($i = 0; $i < count($players); $i++) { ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td class="left-align"><?= $players[$i]["name"] ?></td>
          <td><?= $players[$i]["local_score"] ?></td>
          <td><?= $players[$i]["stars"] ?></td>
          <td><?= $players[$i]["gold"] ?></td>
          <td><?= $players[$i]["silver"] ?></td>
          <td><?= $players[$i]["bronze"] ?></td>
          <td><?= $players[$i]["medals_tot"] ?></td>
          <!--<td><?= $players[$i]["global_score"] ?></td>-->
          <?php
          for ($day = 1; $day <= 25; $day++) {

            $puzzle_open = new DateTime($event_year . '-12-' . $day . "T" . "05:00:00Z");

            for ($star_num = 1; $star_num <= 2; $star_num++) { ?>

              <td class="<?= 'star-table-' . $star_num ?>">
              <?php $cell = "";

              if (array_key_exists($day, $players[$i]["completion_day_level"])
              && array_key_exists($star_num,
              $players[$i]["completion_day_level"][$day])) {

                $solve_ts = $players[$i]["completion_day_level"][$day][$star_num]["get_star_ts"];

                $tooltip = '<span>Day ' . $day . ' Star '. $star_num;
                $tooltip .= '<br>Obtained ' . gmdate("Y-m-d H:i:s", $solve_ts) . " UTC";
                $tooltip .= "<br>Solve time: " . nice_solve_time($puzzle_open, $solve_ts);
                if (array_key_exists("gold", $players[$i]["medals"][$day][$star_num])) {
                  $tooltip .= '<br><span class="gold">Gold</span> medal awarded!';
                } else if (array_key_exists("silver", $players[$i]["medals"][$day][$star_num])) {
                  $tooltip .= '<br><span class="silver">Silver</span> medal awarded!';
                } else if (array_key_exists("bronze", $players[$i]["medals"][$day][$star_num])) {
                  $tooltip .= '<br><span class="bronze">Bronze</span> medal awarded!';
                }
                $tooltip .= '</span>';

                $cell = '<span class="star star-gold"><a href="#" class="tooltip">*' . $tooltip . '</a></span>';
                if (array_key_exists("gold", $players[$i]["medals"][$day][$star_num])) {
                  $cell .= '<br><a href="#" class="tooltip">' . $medal_gold_img . $tooltip . '</a>';
                } else if (array_key_exists("silver", $players[$i]["medals"][$day][$star_num])) {
                  $cell .= '<br><a href="#" class="tooltip">' . $medal_silver_img . $tooltip . '</a>';
                } else if (array_key_exists("bronze", $players[$i]["medals"][$day][$star_num])) {
                  $cell .= '<br><a href="#" class="tooltip">' . $medal_bronze_img . $tooltip . '</a>';
                }

              }
              echo $cell; ?>
              </td>
              <?php
            }
          }
          ?>

        </tr>
      <?php } ?>

    </table>

    <p>Sort by: <?php
    $bits = array();
    $entries = array("local_score"=>"score", "stars"=>"stars", "medals_tot"=>"total medals", "gold"=>"gold medals", "silver"=>"silver medals", "bronze"=>"bronze medals");
    foreach ($entries as $field => $text) {
      if ($field == $sort_field) {
        array_push($bits, '<strong>' . $text . '</strong>');
      } else {
        $url = '<a href="' . parse_url($_SERVER["REQUEST_URI"],
        PHP_URL_PATH) . '?sort_field=' . $field;
        if ($fromGET) {
          $url .= '&boardid=' . $board_id . '&sessionid=' . $session_id;
        }
        $url .= '">' . $text . '</a>';
        array_push($bits, $url);
      }
    }
    echo implode(" &ndash; ", $bits);
    ?>
    </p>

    <p>
      <small>JSON last updated on <?= date('Y-m-d H:i:s T', $data["last_update"]) ?> &mdash; data might be up to 15 minutes old.</small><br>
    </p>

    <p><a href="https://adventofcode.com/<?= $event_year ?>" target="_blank">Advent of Code <?= $event_year ?></a> is a programming challenge created by <a href="http://was.tl/" target="_blank">Eric Wastl</a>.</p>

  <?php }

  /****************************************************************************/
  // OUTPUT ERROR PAGE WITH INSTRUCTIONS

  if (empty($data) || empty($board_id) ||empty($session_id)) { ?>

    <h4>Advent of Code <?= $event_year ?> Private leaderboard viewer</h4>

    This is a simple PHP script that displays an Advent of Code's private leaderboard, including more stats and <strong>medals</strong> for the top three fastest solvers for each of the 50 stars. It was inspired by u/jeroenheijmans's <a href="https://www.reddit.com/r/adventofcode/comments/a4mdtp/chromefirefox_extension_with_charts_for_private/" target="_blank">Chrome/Firefox extension</a>.

    <?php if (empty($data) && (!empty($board_id) || !empty($session_id))) { ?>

      <h4>Oops!</h4>

      <p>There was a problem retrieving the JSON file for private leaderboard: <?php echo $board_id ?>.</p>

      <p>The most likely reason is an incomplete (make sure to get all 64 hex digits!), invalid (you must be a member of the board to view it) or expired (log-in again) session ID.</p>

    <?php } ?>

    <h4>Instructions</h4>

    <p>To make this work you'll need to know the <strong>private leaderboard ID</strong> and your <strong>adventofcode.com session ID</strong>.</p>

    <p>Having those modify this PHP file and edit the values of the <strong>$board_id</strong> and <strong>$session_id</strong> variables at the top of the file, and host the file yourself.</p>

    <p><strong>NOTE: Never share or make your AoC cookie session ID public in any way, as you'll grant strangers access to your AoC account</strong></p>

    <p>Here's how to obtain them:</p>

    <ol>

      <li><p><strong>private leaderboard ID</strong></p>
        <p>Go to the official private leaderboard you want to view and obtain the ID at the end of the URL:</p>
        <img src="guide_leaderboard_id.png" />
        <p>It should be a numeric value around 5 digits long.
      </li>


      <li><p><strong>adventofcode.com session ID</strong></p>

        <p>You'll need to access your adventofcode.com cookie and retrieve your session ID from it. It should be 96 hex digits long, something like this:<br> ff26cf24aa0d4057d7de2454f41c409642b9047b4d0465aeb76ca39783a60b31b0f1a946f24f01e575c05789754df92d</p>

        <p>Navigate to <a href="https://adventofcode.com/<?= $event_year ?>" target="_blank">adventofcode.com</a> and <strong>log in</strong>. Then:</p>

        <p>On <strong>Chrome</strong>:</p>
        <ol>
          <li>Click on the padlock to the left of the URL.</li>
          <li>Click on Cookies. A window will open.</li>
          <li>Expand the entry adventofcode.com to find the session cookie.</li>
          <li>The Content field contains the session ID. Double-click to copy it (make sure to get all 96 hex characters).</li>
        </ol>
        <p><img src="guide_session_Chrome1.png" />&nbsp;<img src="guide_session_Chrome2.png" /></p>

        <p>On <strong>Firefox</strong>:</p>
        <ol>
          <li>Open the Firefox Developer Tools by hitting CTRL+SHIFT+I (or Cmd+Option+I on Mac).</li>
          <li>Open the "Storage" tab.</li>
          <li>Expand "Cookies" and select adventofcode.com</li>
          <li>On the list, click on the "session" entry.</li>
          <li>The "value" column will hold the session ID. Double-click to copy it.</li>
        </ol>
        <p><img src="guide_session_Firefox.png" /></p>
      </li>


  <?php } ?>

  </div>

</body>
</html>
