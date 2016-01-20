<?php

ncurses_init();
$fullscreen = ncurses_newwin (0, 0, 0, 0);
ncurses_getmaxyx($fullscreen,&$a,&$b);
ncurses_delwin($fullscreen);

$pannel=ncurses_newpad(10,10);

ncurses_wborder($pannel, ord('|'), ord('|'), ord('-'), ord('-'),  ord('/'), ord('\\'), ord('\\'), ord('/'));
ncurses_mvwaddstr($pannel, 1, 1, "Test  String2\n");
ncurses_waddstr($pannel, "Test  String3\n");
ncurses_wrefresh($pannel);
sleep(3);
ncurses_end();
echo "Width:$b\nHeight:$a\n";

?>