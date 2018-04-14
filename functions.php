<?php
// url_with_auth_token adds token parameters to url string
// it should be used to wrap all in-domain links to prevent re-authorization on each page inside domain
function url_with_auth_token($url) {
    // use global $query_params that was initialized during auth check on page load
    global $query_params;

    // no auth info, return original url
    if ($query_params['token'] == "" || $query_params['uuid'] == "") {
        echo $url;
        return;
    }

    $uri_parts = explode('?', $url, 2);
    if (isset($uri_parts[1]) && ($uri_parts[1] == "")) {
        $url .= "?";
    } else {
        $url .= "&";
    }

    $url .= "token=".$query_params['token']."&uuid=".$query_params['uuid'];
    echo $url;
}
?>