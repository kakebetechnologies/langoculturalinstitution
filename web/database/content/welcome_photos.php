<?php
declare(strict_types=1);

/* The photographs of the app's opening screen at the start: [file, caption, credit, which part to keep when cropped (top|upper|centre|lower), status].
   Only the warrior photograph is published; the other four wait as drafts and can be published in Admin, Content, App opening photos. */
$supplied = 'Photograph supplied for this project; the photographer is being confirmed.';
return [
    ['assets/img/welcome/warrior-rock.jpg', 'Spear, shield and feathered headdress', $supplied, 'upper', 'published'],
    ['assets/img/welcome/hunter.jpg', 'Costume of a hunter', 'Photo: Ngumenawe, via Wikimedia Commons (resized). CC BY-SA 4.0.', 'upper', 'draft'],
    ['assets/img/welcome/lira-dance.jpg', 'Traditional dance at a cultural gala', 'Photo: Mr HM MOSES, via Wikimedia Commons (resized). CC BY-SA 4.0.', 'centre', 'draft'],
    ['assets/img/welcome/first-generation.jpg', 'Costumes of the first generation', 'Photo: Ngumenawe, via Wikimedia Commons (resized). CC BY-SA 4.0.', 'upper', 'draft'],
    ['assets/img/welcome/harvest.jpg', 'Gathering the harvest', $supplied, 'lower', 'draft'],
];
