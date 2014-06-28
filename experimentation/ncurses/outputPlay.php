<?php

ncurses_init();
$fullscreen = ncurses_newwin (0, 0, 0, 0);
ncurses_getmaxyx($fullscreen,&$a,&$b);
ncurses_wborder($fullscreen, ord('|'), ord('|'), ord('-'), ord('-'),  ord('/'), ord('\\'), ord('\\'), ord('/'));
#ncurses_addstr("test");
#ncurses_mvwaddstr($fullscreen, 5, 5, "   Test  String\n");
ncurses_mvwaddstr($fullscreen, 1, 1, "Test  String2\n");
ncurses_waddstr($fullscreen, "Test  String3\n");
ncurses_wrefresh($fullscreen);
#ncurses_refresh();
sleep(3);
ncurses_end();
echo "Width:$b\nHeight:$a\n";

?>