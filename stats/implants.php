<?php
  // globals
  include_once($_SERVER["DOCUMENT_ROOT"] . "/eoschargen/_includes/config.php");
  include_once($APP["root"] . "/_includes/functions.global.php");
  include_once($APP["root"] . "/_includes/functions.sheet.php");

  include_once($APP["root"] . "/header.php");

  if(!isset($_SESSION)) {
    session_start();
  }

  $implantsArr = getImplants(EMS_echo($_GET['viewsheet']));

  // check if sheet exists. This also validates for account.
  if(isset($sheetArr['characters'][$_GET['viewChar']]['sheets'][$_GET['viewsheet']]) && $sheetArr['characters'][$_GET['viewChar']]['sheets'][$_GET['viewsheet']] != "") {

  $currentSheet = $sheetArr['characters'][$_GET['viewChar']]['sheets'][$_GET['viewsheet']];

  if($sheetArr['characters'][$_GET['viewChar']]['status'] == 'deceased') {
    $DISABLE = "disabled";
  } else if ($currentSheet['status'] != "ontwerp") {
    $DISABLE = "disabled";
  } else {
    $DISABLE = "";
  }

?>
  <div class="wsleft cell">

  </div>

  <div class="menu cell">
    <?=generateMenu('characters');?>
  </div>

  <div class="main cell">
    <div class="content">

      <h1>Augmentations</h1>

      <div class="row">
        <a href="<?=$APP['header']?>/stats/sheets.php?viewChar=<?=$_GET['viewChar']?>&amp;viewsheet=<?=$_GET['viewsheet']?>" class="button">
          <i class="fas fa-arrow-left"></i>&nbsp;Back
        </a>
      </div>

      <hr/>

      <?php
        if(!isset($_COOKIE['implantDialog'])) {
      ?>
      <div class="dialog">

        <h2><i class="fas fa-info-circle"></i>&nbsp;Symbionts, prosthetics, cybernetics, and other implants.</h2>

        <p>Here you can manage any form of augmentation your character could have.<br/>For the full ruling on augmentations and the sorts, see <a class="cyan underline" href="http://eosfrontier.space/handboeken/boek-2-crafting-economy" target="_blank">rulebook 2 (opens in a new tab)</a>.</p>
        <br/>

        <button class="button" onclick="ecc_setCookie('implantDialog','hide','3');$(this).parent().fadeOut();">
          <i class="fas fa-check green"></i>&nbsp;Understood, hide this message please.
        </button><br/>
      </div>
      <?php
        }
      ?>

      <div id="activeImplants" class="ws-bot-2">
      <?php

        // check for augments, then, loop through them.
        if(isset($implantsArr) && count($implantsArr) > 0 && $implantsArr !== false) {

          $printresult = "<h3>Current augmentations:</h3>";

          foreach($implantsArr AS $implant) {

            if($implant['accountID'] == $TIJDELIJKEID && $implant['status'] != 'removed') {

              // non-skill related implants
              if($implant['type'] == 'flavour') {

                $printresult .= "<div class=\"implant flavour\">"
                    ."<div class=\"block\">"
                      ."<p>Description:&nbsp;<br class=\"hidden-xs\"/>".EMS_echo($implant['description'])."</p>"
                    ."</div>"
                    . "<div class=\"block\">"
                      ."Type:&nbsp;<br class=\"hidden-xs\"/>".$implant['type']
                    ."</div>"
                    . "<div class=\"block hidden-xs\">&nbsp;</div>"
                    // . "<div class=\"block smflex hidden-xs\">&nbsp;</div>"
                    . "<div class=\"block smflex\">"
                      ."<button type=\"button\" onclick=\"disableButtonGroup(this,3);IM_removeImplant('".$_GET['viewsheet']."','".$implant['modifierID']."');return false;\" class=\"button blue no-bg $DISABLE\" name=\"button\"><i class=\"fas fa-trash-alt\"></i>&nbsp;Unplug</button>"
                    ."</div>"
                . "</div>";

              // bionics and symbionts! can emulate skills, thus show different info.
              } else {

                $printresult .= "<div class=\"implant ".$implant['type']."\">"
                    ."<div class=\"block\">"
                      ."<p>Description:&nbsp;<br/>".EMS_echo($implant['description'])."</p>"
                    ."</div>"
                    . "<div class=\"block\">"
                      ."<span class=\"hidden-xs\">Type:&nbsp;<br/></span>".$implant['type']
                    ."</div>"
                    . "<div class=\"block\">"
                      ."<span class=\"hidden-xs\">Skill:&nbsp;<br/></span>".$implant['name'] .", lvl ".(int)$implant['skillgroup_level']
                    ."</div>"
                    // . "<div class=\"block smflex\">"
                    //   ."Status:&nbsp;<br class=\"hidden-xs\"/>".$implant['status']
                    // ."</div>"
                    . "<div class=\"block smflex\">"
                      ."<button type=\"button\" onclick=\"disableButtonGroup(this,3);IM_removeImplant('".$_GET['viewsheet']."','".$implant['modifierID']."');return false;\" class=\"button blue no-bg $DISABLE\" name=\"button\"><i class=\"fas fa-trash-alt\"></i>&nbsp;Unplug</button>"
                    ."</div>"
                . "</div>";
              }

            }

          }

        // 0 augmentations bound to this character sheet. And that is fine.
        } else {

          $printresult = "<h3>Character sheet does not currently have any augmentations.</h3>";

          $printresult .= "<p class=\"ws-bot-2\">You can add them below. Keep in mind that non-flavour* augmentations have to be obtained in game.</p>";

          $printresult .= "<p class=\"text-muted\">* flavour augments are any form of prosthetic, cybernetics, parasite or other augmentation to the character's body that does <strong>not</strong> directly affect your skill sheet. "
          ."For example, your character might have lost his or her right arm during wartime, and carries a replacement prosthetic."
          ."<br/>flavour augments are completely optional.</p>";

        }

        echo $printresult;

      ?>
      <hr style="opacity:0.25;"/>
      </div>

      <div class="">

        <button type="button" class="button green no-bg <?=$DISABLE?>" onclick="IM_chooseType('<?=@(int)$_GET["viewChar"]?>','<?=@(int)$_GET['viewsheet']?>'); disableButtonGroup(this,1);" name="button"><i class="fas fa-plus"></i><br/>New</button>
        &nbsp;
        <button type="button" class="button" onclick="location.reload();" name="button"><i class="fas fa-redo"></i><br/>Refresh</button>

        <hr style="opacity:0.25;"/>
      </div>

      <div id="implantForm"></div>

    </div>
  </div>

  <div class="wsright cell"></div>
<?php

} else {
  echo "<h1>Error 0451</h1>";
  exit();
}

?>



<?php
  include_once($APP["root"] . "/footer.php");

?>
<script type="text/javascript" src="<?=$APP['header']?>/_includes/js/functions.implants.js"></script>
