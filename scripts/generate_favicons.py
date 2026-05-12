"""
Genera favicon multi-formato a partire da public/assets/images/LOGO.png.

Output in public/:
  - favicon.ico            (16/32/48 multi-size)
  - favicon-32.png
  - favicon-192.png
  - favicon-512.png
  - apple-touch-icon.png   (180x180)

Strategia: il logo originale (577x199) contiene un simbolo uccellino a
sinistra + il testo "FINCH-AI" a destra. Per le favicon estraiamo SOLO
il simbolo (best practice: a 16/32px il testo sarebbe illeggibile).
Lo individuiamo cercando il primo gap di colonne completamente trasparenti
tra l'icona e il testo, poi lo centriamo su canvas quadrato trasparente.
"""

from pathlib import Path
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "public" / "assets" / "images" / "LOGO.png"
OUT = ROOT / "public"


def extract_symbol(
    img: Image.Image, gap_threshold: int = 6, tol: int = 25
) -> Image.Image:
    """Isola il simbolo (uccellino) e ne rende trasparente lo sfondo.

    Il LOGO.png originale ha sfondo bianco opaco (non trasparente). Strategia:
      1. Ricava il colore di sfondo dal pixel (0,0).
      2. Calcola, per ogni colonna, il numero di pixel "contenuto" (differiscono
         dal bg oltre `tol`).
      3. Scandisce da sinistra: trova il primo blocco di contenuto, poi il primo
         gap orizzontale di almeno `gap_threshold` colonne vuote — è il confine
         tra simbolo e testo.
      4. Ritaglia [start..gap_start] orizzontalmente, applica bbox verticale.
      5. Converte tutti i pixel "bg" in alpha=0 così la favicon ha sfondo
         trasparente (utile per le SERP Google in dark/light mode).
    """
    img = img.convert("RGBA")
    w, h = img.size
    px = img.load()
    bg = px[0, 0]

    def is_content(p) -> bool:
        return any(abs(p[i] - bg[i]) > tol for i in range(3))

    col_content = [
        sum(1 for y in range(h) if is_content(px[x, y])) for x in range(w)
    ]

    start = next((x for x, c in enumerate(col_content) if c > 0), 0)

    # Il confine tra simbolo e testo non è sempre un gap a zero — può essere
    # solo un drop netto di densità. Cerca il primo blocco di N colonne con
    # densità < `low_thresh`, DOPO aver visto almeno un picco di contenuto alto.
    peak = max(col_content) if col_content else 0
    low_thresh = max(15, peak // 8)
    peak_thresh = peak // 2

    gap_start = w
    saw_peak = False
    low_run = 0
    for x in range(start, w):
        if col_content[x] >= peak_thresh:
            saw_peak = True
            low_run = 0
        elif saw_peak and col_content[x] < low_thresh:
            low_run += 1
            if low_run >= gap_threshold:
                gap_start = x - low_run + 1
                break
        else:
            low_run = 0

    symbol = img.crop((start, 0, gap_start, h))

    # converti bg → trasparente con anti-alias morbido
    sym_px = symbol.load()
    sw, sh = symbol.size
    for y in range(sh):
        for x in range(sw):
            r, g, b, a = sym_px[x, y]
            dist = max(abs(r - bg[0]), abs(g - bg[1]), abs(b - bg[2]))
            if dist <= tol:
                sym_px[x, y] = (r, g, b, 0)
            elif dist < tol * 3:
                # fade morbido per i bordi anti-aliased
                new_a = int(a * min(1.0, (dist - tol) / (tol * 2)))
                sym_px[x, y] = (r, g, b, new_a)

    bbox = symbol.getbbox()
    if bbox:
        symbol = symbol.crop(bbox)
    return symbol


def square_canvas(img: Image.Image, size: int) -> Image.Image:
    """Ridimensiona img mantenendo aspect ratio e la centra su canvas quadrato trasparente."""
    img = img.convert("RGBA")
    w, h = img.size
    scale = size / max(w, h)
    new_w, new_h = int(round(w * scale)), int(round(h * scale))
    resized = img.resize((new_w, new_h), Image.LANCZOS)
    canvas = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    canvas.paste(resized, ((size - new_w) // 2, (size - new_h) // 2), resized)
    return canvas


def main() -> None:
    if not SRC.exists():
        raise SystemExit(f"Sorgente non trovata: {SRC}")

    src = Image.open(SRC)
    print(f"Sorgente: {SRC.name}  {src.size}  mode={src.mode}")

    symbol = extract_symbol(src)
    print(f"Simbolo isolato: {symbol.size}")
    src = symbol

    sizes_png = {
        "favicon-32.png": 32,
        "favicon-192.png": 192,
        "favicon-512.png": 512,
        "apple-touch-icon.png": 180,
    }

    for name, size in sizes_png.items():
        out = OUT / name
        square_canvas(src, size).save(out, "PNG", optimize=True)
        print(f"  wrote {name}  {size}x{size}")

    ico_sizes = [(16, 16), (32, 32), (48, 48)]
    ico_path = OUT / "favicon.ico"
    # Pillow ICO: passa la versione più grande come base e lista `sizes`,
    # Pillow ridimensiona e impacchetta tutte le varianti nel singolo .ico
    square_canvas(src, 48).save(ico_path, format="ICO", sizes=ico_sizes)
    print(f"  wrote favicon.ico  multi-size {ico_sizes}")


if __name__ == "__main__":
    main()
