<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if ($message): ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <h2>Create New Badge</h2>
    <form method="post" action="">
        <?php wp_nonce_field('mkwa_create_badge'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="title">Title</label></th>
                <td><input name="title" type="text" id="title" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="description">Description</label></th>
                <td><textarea name="description" id="description" class="large-text" rows="3" required></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="badge_type">Badge Type</label></th>
                <td>
                    <select name="badge_type" id="badge_type" required>
                        <option value="standard">Standard</option>
                        <option value="cultural">Cultural</option>
                        <option value="community">Community</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="category">Category</label></th>
                <td>
                    <select name="category" id="category" required>
                        <option value="fitness">Fitness</option>
                        <option value="cultural">Cultural</option>
                        <option value="community">Community</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="points_required">Points Required</label></th>
                <td><input name="points_required" type="number" id="points_required" class="small-text" min="0" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="icon_url">Badge Icon</label></th>
                <td>
                    <div class="mkwa-icon-upload">
                        <input name="icon_url" type="text" id="icon_url" class="regular-text" required>
                        <button type="button" class="button" id="upload_icon_button">Upload Icon</button>
                        <div class="icon-preview">
                            <img id="icon_preview" src="" style="display:none;max-width:100px;max-height:100px;margin-top:10px;">
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="mkwa_create_badge" class="button button-primary" value="Create Badge">
        </p>
    </form>

    <h2>Existing Badges</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Icon</th>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
                <th>Category</th>
                <th>Points Required</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($badges as $badge): ?>
                <tr>
                    <td><img src="<?php echo esc_url($badge['icon_url']); ?>" width="32" height="32" alt=""></td>
                    <td><?php echo esc_html($badge['title']); ?></td>
                    <td><?php echo esc_html($badge['description']); ?></td>
                    <td><?php echo esc_html($badge['badge_type']); ?></td>
                    <td><?php echo esc_html($badge['category']); ?></td>
                    <td><?php echo esc_html($badge['points_required']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>