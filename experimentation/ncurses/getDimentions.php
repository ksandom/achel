<?php

ncurses_init();
$fullscreen = ncurses_newwin (0, 0, 0, 0);
ncurses_getmaxyx($fullscreen,&$a,&$b);
ncurses_wborder($fullscreen, ord('|'), ord('|'), ord('-'), ord('-'),  ord('/'), ord('\\'), ord('\\'), ord('/'));
ncurses_wrefresh($fullscreen);
sleep(3);
ncurses_end();
echo "Width:$b\nHeight:$a\n";

?>