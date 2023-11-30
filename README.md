# This is fork from https://github.com/meithan/AoCBoard with teams support

You can fill teams.json with aoc id's of team members and get team leaderboard.
Team total score calculated as average of team members scores.

# AoCBoard - an Advent of Code private leaderboard viewer

![alt text](https://github.com/meithan/AoCBoard/blob/master/screenshot.png "Screenshot")

This is a simple PHP script that displays an Advent of Code's private leaderboard, including more stats and **medals** for the top three fastest solvers for each of the 50 stars. It was inspired by u/jeroenheijmans's [Chrome/Firefox extension](https://www.reddit.com/r/adventofcode/comments/a4mdtp/chromefirefox_extension_with_charts_for_private/).

To make this work you'll need to know the **private leaderboard ID** and your **adventofcode.com session ID**. If you don't know these, scroll down for instructions on obtaining them.

Having those, edit the values of the **$board_id** and **$session_id** variables in the `config.php` file. You can also change the **$year** variable to view the leaderboard for a different year. Now you can view `leaderboard.php` on your webserver.

If you don't have a webserver but have access to a terminal and PHP, you can quickly host one locally on your computer by navigating to where the files are and executing

`php -S 127.0.0.1:8000 -t .`

Then you can view the leaderboard by going to `localhost:8000/leaderboard.php` on your browser.

### private leaderboard ID ###

Go to the private leaderboard you want to view on the AoC website and obtain the ID at the end of the URL:

![alt text](https://github.com/meithan/AoCBoard/blob/master/guide_leaderboard_id.png "Leaderboard ID")

It should be a numeric value around 5 digits long.

### adventofcode.com session ID ###

**NOTE: _Never_ share or make public in any way your AoC cookie session ID, as you'll grant strangers access to your AoC account!**

You'll need to access your adventofcode.com cookie and retrieve your session ID from it. It should be 96 hex digits long, something like this:

`ff26cf24aa0d4057d7de2454f41c409642b9047b4d0465aeb76ca39783a60b31b0f1a946f24f01e575c05789754df92d`

Navigate to [adventofcode.com](https://adventofcode.com/) and **log in**. Then:

On **Chrome**:

1. Click on the padlock to the left of the URL.
2. Click on Cookies. A window will open.
3. Expand the entry adventofcode.com to find the session cookie.
4. The Content field contains the session ID. Double-click to copy it (make sure to get all 96 hex characters).

![alt text](https://github.com/meithan/AoCBoard/blob/master/guide_session_Chrome1.png "Chrome help 1") ![alt text](https://github.com/meithan/AoCBoard/blob/master/guide_session_Chrome2.png "Chrome help 2")

On **Firefox**:

1. Open the Firefox Developer Tools by hitting CTRL+SHIFT+I (or Cmd+Option+I on Mac).
2. Open the "Storage" tab.
3. Expand "Cookies" and select adventofcode.com.
4. On the list, click on the "session" entry.
5. The "value" column will hold the session ID. Double-click to copy it.

![alt text](https://github.com/meithan/AoCBoard/blob/master/guide_session_Firefox.png "Firefox help")
