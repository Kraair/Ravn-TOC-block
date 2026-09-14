=== Ravn TOC Block ===
Contributors: kraair
Tags: table of contents, toc, gutenberg block, navigation, generateblocks
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Een echt Gutenberg-block dat automatisch een inhoudsopgave opbouwt uit de titels van de pagina, met smooth scroll en instelbare kop-niveaus per breekpunt.

== Description ==

Ravn TOC Block voegt een Gutenberg-block "Inhoudsopgave" toe dat automatisch een inhoudsopgave opbouwt uit de titels (headings) op de pagina. Het werkt met `core/heading`, GenerateBlocks (v1 Headline en v2 Text), en daarnaast automatisch met vrijwel elk blok van een andere page builder waarvan de eigen HTML met een `<h1>`-`<h6>`-tag begint.

**Functies**

* Automatische detectie van titels in de hoofdcontent van het bericht/de pagina.
* Apart instelbaar welke kop-niveaus (H1 t/m H6) zichtbaar zijn op desktop en welke op mobiel.
* Smooth scroll naar de bijbehorende titel bij een klik op een link.
* Optionele highlighting van de actieve sectie tijdens scrollen.
* Volledig stijlbaar via de standaard block-instellingen (kleur, ruimte, typografie, rand, uitlijning) plus een apart paneel voor link-styling (lettergrootte, dikte, onderstreping, linkkleur en hoverkleur).
* Uitbreidbaar via het filter `toc_block_heading_block_defs` om extra block-types als titel te laten herkennen.

== Installation ==

1. Upload de map `toc-block` naar `/wp-content/plugins/`, of upload de zip via Plugins > Nieuwe plugin > Plugin uploaden in het dashboard.
2. Activeer "Ravn TOC Block" onder Plugins.
3. Voeg in de block-editor het block "Inhoudsopgave" toe.

== Frequently Asked Questions ==

= Welke page builders worden ondersteund? =

`core/heading` en GenerateBlocks worden direct herkend. Daarnaast wordt automatisch elk blok herkend waarvan de eigen opgeslagen HTML met een `<h1>`-`<h6>`-tag begint — dit werkt in de praktijk met vrijwel elke page builder.

= Kan ik zelf extra block-types laten herkennen? =

Ja, via het filter `toc_block_heading_block_defs`:

`
add_filter( 'toc_block_heading_block_defs', function ( $defs ) {
    $defs['mijn-plugin/kop'] = array( 'tag_attr' => 'tag', 'text_attr' => 'text' );
    return $defs;
} );
`

= Werkt het block ook in widgets, headers of footers? =

Nee, het block werkt op de hoofdcontent van het bericht/de pagina zelf, niet op koppen in widgets, headers, footers of losse template-parts.

= Hoe pas ik het responsive breekpunt aan? =

De desktop/mobiel-keuze gebruikt standaard een breekpunt van 768px, ingesteld in `style.css`. Pas de media queries onderin dat bestand aan als jouw thema een ander mobiel-breekpunt gebruikt.

= Mijn sticky header overlapt de titel na het scrollen, hoe fix ik dat? =

In `style.css` staat de regel `:is(h1,h2,h3,h4,h5,h6)[id] { scroll-margin-top: 100px; }`. Pas de 100px aan naar de werkelijke hoogte van jouw sticky header.

== CSS-klassen ==

* `.toc-block-nav` / `.toc-block-title` / `.toc-block-list` / `.toc-block-item`
* `.toc-block-level-1` t/m `.toc-block-level-6` (per kop-niveau, voor eigen inspringing)
* `.toc-block-hide-desktop` / `.toc-block-hide-mobile` (responsive zichtbaarheid)

== Changelog ==

= 1.2.0 =
* Huidige release.

== Upgrade Notice ==

= 1.2.0 =
Huidige release.
