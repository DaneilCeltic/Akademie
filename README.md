# Akademie nevšedního vzdělávání - Moderní HTML/CSS Šablony

## Přehled

Tato složka obsahuje kompletní sadu moderních HTML/CSS šablon pro webové stránky Akademie nevšedního vzdělávání. Šablony jsou navrženy s důrazem na semináře a rezervační systém, s možností snadné integrace do WordPress a Elementor.

## Struktura souborů

### Hlavní soubory
- `index.html` - Hlavní stránka s katalogem seminářů
- `seminar-detail.html` - Detailní stránka semináře
- `styles.css` - Hlavní CSS styly
- `seminar-detail.css` - Styly pro detailní stránku semináře
- `script.js` - JavaScript funkcionalita

### Klíčové funkce

#### 1. Responzivní design
- Plně responzivní design pro všechna zařízení
- Mobilní menu s postranním panelem
- Optimalizace pro tablet a desktop

#### 2. Katalog seminářů
- Filtrování seminářů podle kategorií
- Interaktivní karty seminářů
- Rychlá akce "Rezervovat" a "Více info"

#### 3. Rezervační systém
- Kompletní formulář pro rezervaci
- Předvyplnění z katalogu seminářů
- Notifikační systém pro úspěšné odeslání

#### 4. Moderní UX/UI
- Smooth scrolling
- Hover efekty a animace
- Modální okna pro detaily seminářů
- Scroll-to-top tlačítko

## Integrace do WordPress/Elementor

### Příprava pro WordPress
1. **Styly**: Všechny CSS styly lze snadno importovat do WordPress theme
2. **Struktura**: HTML je strukturováno pro snadnou konverzi na PHP šablony
3. **Elementor kompatibilita**: Použity standardní CSS třídy kompatibilní s Elementor

### Doporučené kroky integrace
1. Importujte CSS do `style.css` vašeho WordPress theme
2. Rozdělete HTML na části (header, footer, content)
3. Vytvořte custom post type pro semináře
4. Implementujte kontaktní formulář pomocí Contact Form 7 nebo podobného pluginu

## Technické detaily

### Použité technologie
- **HTML5**: Sémantická struktura
- **CSS3**: Flexbox, Grid, animace
- **JavaScript**: Vanilla JS pro interaktivitu
- **Font Awesome**: Ikony
- **Google Fonts**: Typografie (Inter)

### Barevná paleta
- Primární: #3b82f6 (modrá)
- Sekundární: #1f2937 (tmavě šedá)
- Accent: #10b981 (zelená)
- Pozadí: #f8fafc (světle šedá)

### Breakpointy
- Mobile: < 480px
- Tablet: 481px - 768px
- Desktop: > 768px

## Obsah a přizpůsobení

### Semináře
Aktuálně zahrnuté semináře:
- Etiketa v písemné komunikaci
- Jak se v práci prací neunavit
- Anti-GDPR a šikanózní podání
- Akademie zad

### Postranní menu
Kompletní menu obsahuje:
- Semináře (hlavní)
- O nás
- Proč s námi
- Naše knihy
- Naše "léky"
- Kulturní akce
- E-shop
- Videogalerie
- Kontakt

## Přizpůsobení

### Změna obrázků
- Aktuálně používány stock obrázky z Unsplash
- Nahraďte URL obrázků vlastními

### Úprava obsahu
- Všechny texty lze snadno editovat v HTML souborech
- Údaje o seminářích, kontaktech a službách

### Styling
- Barvy lze změnit v CSS proměnných
- Fonty lze nahradit v Google Fonts importu

## Funkce pro WordPress

### Kontaktní formulář
```php
// Integrace s Contact Form 7
[contact-form-7 id="123" title="Rezervace semináře"]
```

### Custom post type - Semináře
```php
// Registrace custom post type
register_post_type('seminar', array(
    'labels' => array(
        'name' => 'Semináře',
        'singular_name' => 'Seminář'
    ),
    'public' => true,
    'has_archive' => true,
    'supports' => array('title', 'editor', 'thumbnail')
));
```

## Optimalizace

### Performance
- Minifikované CSS a JS pro produkci
- Optimalizované obrázky
- Lazy loading pro obrázky

### SEO
- Sémantické HTML tagy
- Meta tagy pro každou stránku
- Správná struktura nadpisů (H1-H6)

## Podpora a rozšíření

### Možná rozšíření
- Kalendář seminářů
- Online platební systém
- Uživatelské účty
- Hodnocení seminářů
- Newsletter systém

### Kompatibilita
- Všechny moderní prohlížeče
- WordPress 5.0+
- Elementor 3.0+
- Contact Form 7
- WooCommerce ready

## Licence
Šablony jsou vytvořeny pro specifické použití Akademie nevšedního vzdělávání.