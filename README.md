# Ravn TOC Block

Een Gutenberg-block voor WordPress dat automatisch een inhoudsopgave opbouwt uit de titels van een pagina — met smooth scroll, optionele highlighting van de actieve sectie, en apart instelbare kop-niveaus voor desktop en mobiel.

## Functies

- Automatische detectie van titels in de hoofdcontent van het bericht/de pagina.
- Werkt met `core/heading`, GenerateBlocks (v1 Headline en v2 Text), en vangt daarnaast automatisch vrijwel elke andere page builder af.
- Apart instelbaar welke kop-niveaus (H1–H6) zichtbaar zijn op desktop en welke op mobiel.
- Smooth scroll naar de bijbehorende titel.
- Optionele highlighting van de actieve sectie tijdens scrollen.
- Volledig stijlbaar via de standaard block-instellingen, plus een apart paneel voor link-styling.
- Uitbreidbaar via het filter `toc_block_heading_block_defs`.

## Installatie

1. Upload de map `toc-block` naar `/wp-content/plugins/`, of upload de zip via **Plugins → Nieuwe plugin → Plugin uploaden**.
2. Activeer **Ravn TOC Block** onder Plugins.
3. Voeg in de block-editor het block **Inhoudsopgave** toe.

## Vereisten

- WordPress 6.4 of hoger
- PHP 7.4 of hoger

## Uitbreiden

Extra block-types als titel laten herkennen:

```php
add_filter( 'toc_block_heading_block_defs', function ( $defs ) {
    $defs['mijn-plugin/kop'] = array( 'tag_attr' => 'tag', 'text_attr' => 'text' );
    return $defs;
} );
```

## Beperkingen

Werkt op de hoofdcontent van het bericht/de pagina zelf, niet op koppen in widgets, headers, footers of losse template-parts.

## Licentie

GPL v2 or later — zie [LICENSE](LICENSE).
