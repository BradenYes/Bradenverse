<?php
$title = 'Rules';
require_once('inc/header.php');
if(!empty($_SESSION['username'])) {
    $row = initUser($_SESSION['username'], true);
} else {
    $is_general = true;
    require_once('elements/user-sidebar.php');
}
?>
<div class="main-column post-list-outline" id="help">
    <h2 class="label">Site Rules</h2>
    <div id="guide" class="help-content">
        <div class="num1">
            <h2>Bradenverse Rules</h2>
            <p>To help keep the site in order, there are some rules we'd like you to follow. This site is much more open-ended than other Miiverse clones, and as such the rules are mostly set by the communities you visit, but there are still some ground rules so anarchy doesn't break out. Please read this short list before using the site.</p>
            <h3>Just no NSFW/NSFL or Racial slurs</h3>
            <p>pretty self-explanatory</p>
            <h3>Legal Information</h3>
            <p>Bradenverse is not, in any way, associated with Nintendo Co, Ltd. or Hatena Co, Ltd. Nintendo and Hatena have no involvement with this service, and neither company maintains, endorses, sponsors or contributes to this. <a href="http://bradenversethe2nd.rf.gd/info/contact">If anything found on this site infringes on your rights, contact us and we can fix it.</a></p>
        </div>
    </div>
</div>
<?php
require_once('inc/footer.php');
?>