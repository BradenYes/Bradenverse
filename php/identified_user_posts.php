<?php
$title = 'Posts from Verified Users';
require_once('inc/header.php');
if(!empty($_SESSION['username'])) {
    $row = initUser($_SESSION['username'], true);
} else {
    $is_general = true;
    require_once('elements/user-sidebar.php');
}
?>
<div class="main-column"><div class="post-list-outline">
<div id="image-header-content">
  <span class="image-header-title">
    <span class="title">Posts from Verified Users</span>
    <span class="text">I do not McLove these posts Braden</span>
  </span>
  <img src="/assets/img/identified-user.png">
</div>
<?php
$bruh = 2;
                if(empty($_GET['offset'])) {
                    $_GET['offset'] = 0;
                }
                $stmt = $db->prepare('SELECT posts.id, created_by, posts.created_at, feeling, body, image, yt, posts.community, communities.name, communities.icon, sensitive_content, username, nickname, avatar, has_mh, users.level, community_admins.level AS community_level, (SELECT COUNT(*) FROM empathies WHERE target = posts.id AND type = 0) AS empathy_count, (SELECT COUNT(*) FROM replies WHERE post = posts.id AND status = 0) AS reply_count, (SELECT COUNT(*) FROM empathies WHERE target = posts.id AND type = 0 AND source = ?) AS empathy_added FROM posts LEFT JOIN users ON created_by = users.id LEFT JOIN communities ON communities.id = posts.community LEFT JOIN community_admins ON community_admins.user = users.id AND posts.community = community_admins.community WHERE users.level > 1 AND IF(users.level < ? OR (SELECT IFNULL(community_admins.level, 0) FROM community_admins WHERE user = ? AND community = posts.community LIMIT 1) < (SELECT IFNULL(community_admins.level, 0) FROM community_admins WHERE user = created_by AND community = posts.community LIMIT 1), 1, created_by NOT IN (SELECT target FROM blocks WHERE source = ? UNION SELECT source FROM blocks WHERE target = ?)) AND posts.status = 0 ORDER BY posts.id DESC LIMIT 20 OFFSET ?');
                $stmt->bind_param('iiiiii', $_SESSION['id'], $_SESSION['level'], $_SESSION['id'], $_SESSION['id'], $_SESSION['id'], $_GET['offset']);
                $stmt->execute();
                if($stmt->error) {
                    showNoContent('An error occurred while grabbing posts from the database.');
                }
                $result = $stmt->get_result();
                if($result->num_rows === 0) {
                    if($_GET['offset'] == 0) {
                        showNoContent('No posts.');
                    }
                } else {
                    echo '<div class="list post-list js-post-list test-identified-post-list" data-next-page-url="?offset=' . ($_GET['offset'] + 20) . '">';
                    while($row = $result->fetch_assoc()) {
                        require('elements/post.php');
                    }
                    echo '</div>';
                }
            ?>
</div></div>
<?php
require_once('inc/footer.php');
?>