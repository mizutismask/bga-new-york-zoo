# How to extract NYZ images from pdf

```
#finds colorspace 
identify -format "%[colorspace]\n"
mogrify -path ./resized -resize 700 -quality 100 \*.jpg
montage `ls -v ./resized` -tile 2 -geometry 700x569+0+0 board2P.jpg
montage `ls -v .` -tile 4 -geometry 560x452+0+0 board4P.jpg
mogrify -path ./cropped -shave 35x35 -resize 700 -quality 100 \*.png
```

```
#redimensionne a la moitié, et fait un montage sans bordure, adapté à la taille de l'image la plus grande, chaque image collée en haut #de la grille -> pb, les petits ne respectent pas la largeur
montage `ls -v .` -resize 50% -tile 5 -geometry +0+0 -gravity north tiles.jpg
#renomme les images en les comptant
n=1; for f in *.png; do mv "$f" "patch-face-$((n++)).png"; done
```

# State diagram
## How to generate it 

```
php.exe generateStateDiagram.php > stateDiagram.dot
```

## How to print it 
- copy/paste the code in stateDiagram.dot to http://www.webgraphviz.com/
- remove unwanted elements with developers tools and print with the browser