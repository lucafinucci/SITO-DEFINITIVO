# Favicon Files Needed

Per completare il setup SEO, è necessario generare i seguenti file favicon dal logo LOGO.png:

## File necessari:

1. **favicon-16x16.png** - Favicon piccolo (16x16px)
2. **favicon-32x32.png** - Favicon standard (32x32px)
3. **apple-touch-icon.png** - Icona per dispositivi Apple (180x180px)
4. **android-chrome-192x192.png** - Icona Android piccola (192x192px)
5. **android-chrome-512x512.png** - Icona Android grande (512x512px)
6. **og-image.png** - Immagine OpenGraph per social media (1200x630px)

## Come generare:

### Opzione 1: Strumenti online
- Visita https://realfavicongenerator.net/
- Carica il file LOGO.png
- Genera tutti i formati necessari
- Scarica e posiziona i file in questa cartella

### Opzione 2: ImageMagick (da terminale)
```bash
# Favicon 16x16
magick convert LOGO.png -resize 16x16 favicon-16x16.png

# Favicon 32x32
magick convert LOGO.png -resize 32x32 favicon-32x32.png

# Apple Touch Icon 180x180
magick convert LOGO.png -resize 180x180 apple-touch-icon.png

# Android Chrome 192x192
magick convert LOGO.png -resize 192x192 android-chrome-192x192.png

# Android Chrome 512x512
magick convert LOGO.png -resize 512x512 android-chrome-512x512.png

# OpenGraph Image 1200x630 (con padding/background)
magick convert LOGO.png -resize 800x800 -gravity center -extent 1200x630 -background "#0a1a2b" og-image.png
```

### Opzione 3: Photoshop/GIMP
- Apri LOGO.png
- Esporta nelle dimensioni richieste
- Salva come PNG con trasparenza

## Note:
- I favicon devono avere sfondo trasparente o bianco
- L'immagine OpenGraph deve avere sfondo solido (#0a1a2b - tema dark del sito)
- Tutti i file devono essere in formato PNG
- La qualità deve essere ottimale per rendering nitido su tutti i dispositivi
