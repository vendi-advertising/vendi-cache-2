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
                <?php esc_html_e( 'Vendi Cache (and before that, Wordfence) previously offered "basic" and "enhanced" or "disk-based" caching (also call the Falcon engine). After many years of confusion with users, advances in technology, and testing we have decided to discontinue the "basic" caching and only offer the "enhanced" mode.', 'vendi-cache' ); ?>
            </p>
    </div>

    <div class="information">
        <h3>
            <?php esc_html_e( 'The enhanced caching mode does the following:', 'vendi-cache' ); ?>
        </h3>
        <ol>
            <li>
                <?php esc_html_e( 'The first time that Vendi Cache is enabled it writes special instructions to your server&rsquo;s &ldquo;.htaccess&rdquo; file.', 'vendi-cache' ); ?>
            </li>
            <li>
                <?php esc_html_e( 'When a page is requested for the first time, Vendi Cache copies the resulting HTML to a specially-named file on your server.', 'vendi-cache' ); ?>
            </li>
            <li>
                <?php esc_html_e( 'It also then GZIP&rsquo;s the file and writes that side-by-side with the previous file.', 'vendi-cache' ); ?>
            </li>
            <li>
                <?php esc_html_e( 'This HTML is sent to the person just as they normally would receive it.', 'vendi-cache' ); ?>
            </li>
            <li>
                <?php esc_html_e( 'When another person requests the same page, instead of routing the request through WordPress, your server itself (Apache, Nginx, IIS) is able to send them the HTML directly. Additionally, if the person&rsquo;s browser supports GZIP (and pretty much every browser from the past 5 years does), they&rsquo;ll be given the much smaller version of the HTML which makes their experience even faster.', 'vendi-cache' ); ?>
            </li>
        </ol>
    </div>

</form>
