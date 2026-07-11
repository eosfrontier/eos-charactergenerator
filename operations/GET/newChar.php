<h1>Create a character</h1>
<hr />
<?php
include_once  APP_ROOT . "/_includes/functions.playercap.php";
echo '<form method="POST" action="' . $APP['header'] . '/index.php">';
$factions = array('aquila', 'dugo', 'ekanesh', 'pendzal', 'sona');
?>


<div class="formitem center-xs">
  <label for="faction_dropdown">First, choose your faction:</label><br>
  
  <?php
  $faction_status = [];
  foreach ($factions as $faction) {
      $is_allowed = (get_active_players($faction)->num_rows < 35);
      
      // Capture the output of playerStopAlert() if the faction is full
      $alert_html = '';
      if (!$is_allowed && function_exists('playerStopAlert')) {
          // If the function returns a string, assign it. If it echoes directly, use output buffering.
          ob_start();
          $result = playerStopAlert(ucfirst($faction));
          $alert_html = ob_get_clean();
          if (empty($alert_html)) {
              $alert_html = $result; // fallback if it returns instead of echoing
          }
      }

      $faction_status[$faction] = [
          'allowed' => $is_allowed,
          'alert'   => $alert_html
      ];
  }
  ?>
  <!-- We print the data directly into the HTML context so functions.js can read it globally -->
  <script>
    window.factionAllowedMap = <?php echo json_encode($faction_status); ?>;
  </script>

  <!-- Notice the change: we call handleFactionChange() instead of the old function -->
  <select name="newchar" id="chooseFactionSelect" onchange="handleFactionChange(this.value);">
    <option disabled selected value="">--select a faction--</option>
    <?php foreach ($factions as $faction): ?>
      <option value="<?php echo htmlspecialchars($faction); ?>"><?php echo ucfirst($faction); ?></option>
    <?php endforeach; ?>
  </select>
</div>
<!-- Add a placeholder div somewhere on your page where the server alert text should inject itself -->
<div id="serverAlertPlaceholder" style="display: none;"></div>

<div class="formitem">
  <?php
  // if (isset($_POST['newchar'])) { 
  $printresult .= '<input type="submit" id="createButton" class="button blue" value="Create character"style="display: none;"></input>';

  if ($sheetArr['characters'] && count($sheetArr['characters']) > 0) {
    $printresult .= '&nbsp;<a class="button" href=' . $APP['header'] . '/index.php>Back</a>';
  }

  $printresult .= '</div>
</form>';

  $printresult .= '<div id="fct_aa" class="formitem dialog factionblurb" style="display: block;">'
    . '<h2 class="center-xs"><i class="far fa-lightbulb"></i>&nbsp;Please select a faction.</h2>'
    . '<p>To begin, choose a faction from the dropdown above.</p>'
    . '</div>';

  $printresult .= '<div id="fct_aquila" class="formitem dialog factionblurb">'
    . '<h2 class="center-xs"><i class="far fa-lightbulb"></i>&nbsp;Aquila</h2>'
    . '<p>One of the two biggest and oldest factions. This republic judges citizens by the '
    . 'military service they put in and have a fondness for bureaucracy and universal modularity. '
    . 'They believe everyone should be able to treat a basic injury and love their forcefield systems.</p>'
    . '</div>';

  $printresult .= '<div id="fct_dugo" class="formitem dialog factionblurb">'
    . '<h2 class="center-xs"><i class="far fa-lightbulb"></i>&nbsp;Dugo</h2>'
    . '<p>Ancient rival of the Aquila faction and therefore the other main power. The Dugo society functions on '
    . 'specialists doing their one thing and doing that excellently. Personal responsibility and a quantifiable '
    . 'measure of Honour are the core tenets for this Caste-ruled Empire where 95% passes through the military to '
    . 'gain the right of expressing their soul via a melee weapon they may carry anywhere..</p>'
    . '</div>';

  $printresult .= '<div id="fct_ekanesh" class="formitem dialog factionblurb">'
    . '<h2 class="center-xs"><i class="far fa-lightbulb"></i>&nbsp;Ekanesh</h2>'
    . '<p>A Lost expedition to Eos from centuries past, these ex-Aquila came back changed forever. Following the '
    . 'light of their goddess Maïr on a mission to save humanity from the Alien Threat, their general incompatibility'
    . 'with advanced technology is more than made up for with Psionic powers and alien growths called Symbionts to '
    . 'fill the skill gaps they may face.</p>'
    . '</div>';

  $printresult .= '<div id="fct_pendzal" class="formitem dialog factionblurb">'
    . '<h2 class="center-xs"><i class="far fa-lightbulb"></i>&nbsp;Pendzal</h2>'
    #.  playerStopAlert('Pendzal')
    . '<p>Uncountable clans break up the Pendzal planetary borders with their own territories but work together '
    . 'when they have to. Personal freedom of choice is an inviolable human right to these engineers at heart, and '
    . 'they fought their way out of the Aquila and Dugo in bitter separation wars to gain the recognition they deserved.</p>'
    . '</div>';

  $printresult .= '<div id="fct_sona" class="formitem dialog factionblurb">'
    . '<h2 class="center-xs"><i class="far fa-lightbulb"></i>&nbsp;Sona</h2>'
    . '<p>Financial responsibility equates to legal maturity for these mercantile nomadic-inspired businessmen. Their '
    . 'lush style of living and financial prowess made sure these information brokers secured worlds of their own and '
    . 'managed to abolish all other currencies in favour of their universal standard, the Sonur.</p>'
    . '</div>';
