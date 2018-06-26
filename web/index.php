<?php

$pageTitle = 'PoC - Web App security with Hashicorp Vault';
$flagLink = 'https://europa.eu/european-union/sites/europaeu/files/country_images/flags/flag-%s.jpg';

$data = require_once __DIR__ . '/../data/db/eu.php';
/*
$data = [
   [
       'country' => 'Austria',
       'code' => 'AU',
       'flag' => 'austria',
   ],
   [
       'country' => 'Belgium',
       'code' => 'BE',
       'flag' => 'belgium',
   ],
   [
       'country' => 'Bulgaria',
       'code' => 'BG',
       'flag' => 'bulgaria',
   ],
   [
       'country' => 'Croatia',
       'code' => 'HR',
       'flag' => 'croatia',
   ],
   [
       'country' => 'Cyprus',
       'code' => 'CY',
       'flag' => 'cyprus',
   ],
   [
       'country' => 'Czech Republic',
       'code' => 'CZ',
       'flag' => 'czech-republic',
   ],
   [
       'country' => 'Denmark',
       'code' => 'DK',
       'flag' => 'denmark',
   ],
   [
       'country' => 'Estonia',
       'code' => 'EE',
       'flag' => 'estonia',
   ],
   [
       'country' => 'Finland',
       'code' => 'FI',
       'flag' => 'finland',
   ],
   [
       'country' => 'France',
       'code' => 'FR',
       'flag' => 'france',
   ],
   [
       'country' => 'Germany',
       'code' => 'DE',
       'flag' => 'germany',
   ],
   [
       'country' => 'Greece',
       'code' => 'GR',
       'flag' => 'greece',
   ],
   [
       'country' => 'Hungary',
       'code' => 'HU',
       'flag' => 'hungary',
   ],
   [
       'country' => 'Ireland',
       'code' => 'IE',
       'flag' => 'ireland',
   ],
   [
       'country' => 'Italy',
       'code' => 'IT',
       'flag' => 'italy',
   ],
   [
       'country' => 'Latvia',
       'code' => 'LV',
       'flag' => 'latvian',
   ],
   [
       'country' => 'Lithuania',
       'code' => 'LT',
       'flag' => 'lithuania',
   ],
   [
       'country' => 'Luxembourg',
       'code' => 'LU',
       'flag' => 'luxembourg',
   ],
   [
       'country' => 'Malta',
       'code' => 'MT',
       'flag' => 'malta',
   ],
   [
       'country' => 'Netherlands',
       'code' => 'NL',
       'flag' => 'netherlands',
   ],
   [
       'country' => 'Poland',
       'code' => 'PO',
       'flag' => 'poland',
   ],
   [
       'country' => 'Portugal',
       'code' => 'PT',
       'flag' => 'portugal',
   ],
   [
       'country' => 'Romania',
       'code' => 'RO',
       'flag' => 'romania',
   ],
   [
       'country' => 'Slovakia',
       'code' => 'SK',
       'flag' => 'slovakia',
   ],
   [
       'country' => 'Slovenia',
       'code' => 'SI',
       'flag' => 'slovenia',
   ],
   [
       'country' => 'Spain',
       'code' => 'ES',
       'flag' => 'spain',
   ],
   [
       'country' => 'Sweden',
       'code' => 'SE',
       'flag' => 'sweden',
   ],
   [
       'country' => 'United Kingdom',
       'code' => 'UK',
       'flag' => 'uk',
   ],
];
*/

function escape(string $string): string
{
    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

    <title><?php echo escape($pageTitle) ?></title>
  </head>
  <body>
      <div class="container">
          <h1><?php echo escape($pageTitle) ?></h1>

          <section id="section-intro">
              <h2 class="h2"><?php echo escape('Introduction') ?></h2>
              <p>This is a small application that requires a database, using <a href="https://www.vaultproject.io/" target="_blank" title="Vault by Hashicorp" rel="nofollow">Hashicorp Vault</a> to provide access credentials.</p>
          </section><!-- /#section-intro -->

          <section id="section-data">
              <h2 class="h2"><?php echo escape('EU Member Countries') ?></h2>
              <table class="table">
                  <thead>
                      <tr>
                          <th>Country</th>
                          <th>Code</th>
                          <th>Flag</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ($data as $entry): ?>
                      <tr>
                          <td><?php echo escape($entry['country']) ?></td>
                          <td><?php echo escape($entry['code']) ?></td>
                          <td><img src="<?php echo sprintf($flagLink, strtolower($entry['flag'])) ?>" width="24" height="24" alt="<?php echo escape($entry['country']) ?>"></td>
                      </tr>
                      <?php endforeach ?>
                  </tbody>
              </table>
          </section><!-- /#section-data -->
      </div><!-- /.container -->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
  </body>
</html>
