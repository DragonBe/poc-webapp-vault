<?php

$contents = file_get_contents(__DIR__ . '/.client');
putenv('VAULT_ACCESS_TOKEN=' . $contents);

$pageTitle = 'PoC - Web App security with Hashicorp Vault';
$flagLink = 'https://europa.eu/european-union/sites/europaeu/files/country_images/flags/flag-%s.jpg';


require_once __DIR__ . '/../vendor/autoload.php';

$accessToken = getenv('VAULT_ACCESS_TOKEN');

$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://vault:8200',
    'timeout'  => 2.0,
    'headers' => [
        'X-Vault-Token' => $accessToken,
        'User-Agent' => 'dragonbe/hashicorp-vault/0.0.1 PHP 7.2',
        'Accept' => 'application/json',
    ]
]);

try {
    $responseAccess = $client->request('GET', '/v1/database/creds/webapp');
} catch (\GuzzleHttp\Exception\ClientException $clientException) {
    echo 'Not allowed to retrieve credentials: ' . $clientException->getMessage() . '<br>' . PHP_EOL;
    echo '<a href="/client_token.php">Get new token</a>';
    exit;
}
$responseJson = (string) $responseAccess->getBody();
$responseData = json_decode($responseJson);

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;encoding=%s',
            'mysql',
            3306,
            'europe',
            'utf8'
        ),
        $responseData->data->username,
        $responseData->data->password
    );
} catch (PDOException $PDOException) {
    echo 'Cannot connect to DB: ' . $PDOException->getMessage() . '<br>' . PHP_EOL;
    exit;
}

$queryStmt = $pdo->query('SELECT * FROM `eu_country`');

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
                      <?php while ($entry = $queryStmt->fetch(PDO::FETCH_ASSOC)): ?>
                      <tr>
                          <td><?php echo escape($entry['country']) ?></td>
                          <td><?php echo escape($entry['code']) ?></td>
                          <td><img src="<?php echo sprintf($flagLink, strtolower($entry['flag'])) ?>" width="24" height="24" alt="<?php echo escape($entry['country']) ?>"></td>
                      </tr>
                      <?php endwhile; ?>
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
