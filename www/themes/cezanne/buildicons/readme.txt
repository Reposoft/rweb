This theme uses icons from the Cezanne icon theme
http://www.kde-look.org/content/show.php?content=16479
by Christopher Townson http://christopher.townson.biz 

Creative Commons (Some Rights Reserved)
see: http://creativecommons.org/licenses/by-sa/2.0/

In this folder is a midified version of the script 'buildset'
from the unbuild Cezanne archive.

Just deflate the archive to this folder, and run 
buildset.sh instead of buildset to get
the webbified icons

Use one parameter string with the parameters to modify the ImageMagick conversion
./buildset.sh "-type Palette -colors 32 -background #F0F0EE"
which never worked, but use: "-background white -flatten +matte"
