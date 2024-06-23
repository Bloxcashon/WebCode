<?php
function getUpdatedEntryPoints($conn, $unique_id, $giveawayId) {
    $sql = "SELECT points FROM giveaway WHERE unique_id = '$unique_id' AND giveaway_id = '$giveawayId'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['points'];
}
?>