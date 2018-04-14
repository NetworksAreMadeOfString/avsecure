<?php

// get_challenge retrieves challenge signed by content provider
function get_challenge() {
    $url = 'https://verifier.alpha.avsecure.com/challenge';
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json",
            'method'  => 'GET'
        )
    );
    $context = stream_context_create($options);

    try {
        $content = @file_get_contents($url, false, $context);
        if ($content !== false) {
            return $content;
        }
    } catch (Exception $e) {}
    return "";
}

// build_iframe_url builds iframe url with challenge and signature
function build_iframe_url() {
    $url = 'https://agewall.alpha.avsecure.com';
    $challenge = get_challenge();
    if ($challenge === "") {
        return $url;
    }

    $opts = json_decode($challenge);
    return $url ."?message=" . $opts->{'message'} . "&cp_signature=" . $opts->{'cp_signature'};
}

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>AVsecure</title>
<script src='https://alpha.avsecure.com/static/js/agewall_listener.js'></script>
<style>
    body {
        background-color: black;
    }
</style>
</head>
<body>
<p>
<iframe src="<?php echo build_iframe_url(); ?>" id="iframe" name="iframe_a" frameborder="0" style="position:fixed; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;"></iframe>
</body>
</html>
