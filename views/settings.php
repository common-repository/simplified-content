<div class="wrap">
    <?php screen_icon(); ?>
    <h1>Simplified Content Settings</h1>

    <form method="post" action="options.php">

        <?php settings_fields('lb_options'); ?>


        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row">Sitemap Page Id</th>
                <td>
                    <input type="text" name="lb_options[sitemap-page-id]" class="regular-text"
                           value="<?php echo $options["sitemap-page-id"] ?>">

                    <p class="description">The Wordpress id of the sitemap page.</p>
                </td>
            </tr>
            </tbody>

        </table>

        <hr>

        <h3>Applicable Browsers</h3>

        <p class="description">Configure which browsers will trigger simplified content for this site.  </p>
        <p>WARNING: ONCE SET, ANY BROWSERS SELECTED BELOW WILL CEASE TO FUNCTION FOR ADMINISTRATIVE AND NORMAL WORDPRESS OPERATIONS.</p>

        <table class="form-table">
            <tbody>

            <?php foreach ($browsers as $key => $value) {

                $optionName = "trigger-" . strtolower(str_replace(" ", "", $key));

                ?>

                <tr valign="top">
                    <th scope="row">
                        <?php echo $key; ?>
                    </th>
                    <td>
                        <input type="checkbox" class="check"
                               name="lb_options[<?php echo $optionName; ?>]" <?php echo isset($options[$optionName]) ? 'checked="checked"' : "" ?>/>
                    </td>
                </tr>

            <?php } ?>


            </tbody>
        </table>


        <hr>

        <h3>Wrapper Fragments</h3>
        <p class="description">Configure the header and footer HTML fragments to be appended to all simplified content pages.</p>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row">Header Fragment</th>
                <td>
                    <textarea type="text" name="lb_options[header-html]" rows="10" cols="80"><?php echo $options["header-html"] ?></textarea>

                    <p class="description">Header HTML - should contain the opening &lt;html&gt; and &lt;body&gt; tags and any common banner header for all simplified content.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Footer Fragment</th>
                <td>
                    <textarea type="text" name="lb_options[footer-html]" rows="10" cols="80"><?php echo $options["footer-html"] ?></textarea>

                    <p class="description">Footer HTML - should contain the closing &lt;/body&gt; and &lt;/html&gt; tags and any common footer for all simplified content.</p>
                </td>
            </tr>

            </tbody>

        </table>


        <?php submit_button(); ?>

    </form>

</div>