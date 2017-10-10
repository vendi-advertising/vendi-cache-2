<?php

global $template_maestro;

?>

<h2>Cache Mode</h2>

<form method="post">

    <p>
        <label for="cache-mode-off">
            <?php esc_html_e( 'Disable Vendi Cache', 'vendi-cache' ) ?>
            <input type="radio" name="cache-mode" id="cache-mode-off" value="off" />
        </label>
        <br />
        <label for="cache-mode-on">
            <?php esc_html_e( 'Enable Vendi Cache', 'vendi-cache' ) ?>
            <input type="radio" name="cache-mode" id="cache-mode-on" value="on" />
        </label>
    </p>

    <div class="legacy-note">
            <h3><?php esc_html_e( 'Legacy Note', 'vendi-cache' ) ?></h3>
            <p>
                Vendi Cache (and before that, Wordfence) previously offered "basic" and
                "enhanced" or "disk-based" caching (also call the Falcon engine). After many
                years of confusion with users, advances in technology, and testing we have decided to discontinue the
                "basic" caching and <strong>only</strong> offer the "enhanced" mode.
            </p>
    </div>

    <div class="information">
        <h3>
            The enhanced caching mode does the following:
        </h3>
        <ol>
            <li>
                The first time that Vendi Cache is enabled it writes special instructions to your server's
                <a href="https://codex.wordpress.org/htaccess" target="_blank" rel="noopener"><code>.htaccess</code></a>
                file.
            </li>
            <li>
                When a page is requested for the first time, Vendi Cache copies the resulting HTML to a
                specially-named file on your server.
            </li>
            <li>
                It also then <a href="https://en.wikipedia.org/wiki/Gzip" target="_blank" rel="noopener">GZIP's</a> the
                file and writes that side-by-side with the previous file.
            </li>
            <li>
                This HTML is sent to the person just as they normally would receive it.
            </li>
            <li>
                When <em>another</em> person requests the same page, instead of routing the request through WordPress,
                your <em>server itself</em> (Apache, Nginx, IIS) is able to send them the HTML directly. Additionally,
                if the person's browser supports GZIP (and pretty much every browser from the past 5 years does),
                they'll be given the much smaller version of the HTML which makes their experience even faster.
            </li>
        </ol>
    </div>

</form>
