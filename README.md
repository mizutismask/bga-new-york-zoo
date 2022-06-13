# bga-new-york-zoo

"New York Zoo" game for board game arena boardgamearena

finds colorspace identify -format "%[colorspace]\n" 
mogrify -path ./resized -resize 700 -quality 100 \*.jpg
montage `ls -v ./resized` -tile 2 -geometry 700x569+0+0 board2P.jpg
