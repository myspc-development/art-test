<?php
namespace ArtPulse\Admin;

class ImportExportTab
{
    /**
     * Handle CSV export for posts.
     *
     * @param string $post_type Post type to export.
     */
    public static function exportPostsCsv(string $post_type): void
    {
        $query = new \WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $post_type . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Title', 'Status']);
        foreach ($query->posts as $post_id) {
            $post = get_post($post_id);
            fputcsv($output, [$post_id, $post->post_title, $post->post_status]);
        }
        fclose($output);
        exit;
    }

    /**
     * Render the Import/Export tab contents.
     */
    public static function render(): void
    {
        ?>
        <div id="ap-import-export">
            <h2><?php esc_html_e('Import CSV', 'artpulse'); ?></h2>
            <input type="file" id="ap-csv-file" accept=".csv" />
            <p style="margin-top:10px;">
                <label><input type="checkbox" id="ap-csv-has-header" checked> <?php esc_html_e('File has header row', 'artpulse'); ?></label>
            </p>
            <p>
                <label for="ap-csv-delimiter"><?php esc_html_e('Delimiter', 'artpulse'); ?> </label>
                <select id="ap-csv-delimiter">
                    <option value=",">, (comma)</option>
                    <option value=";">; (semicolon)</option>
                    <option value="tab">Tab</option>
                    <option value="custom"><?php esc_html_e('Custom', 'artpulse'); ?></option>
                </select>
                <input type="text" id="ap-csv-delimiter-custom" style="width:40px;display:none;" maxlength="1" />
            </p>
            <p>
                <label for="ap-csv-skip"><?php esc_html_e('Rows to skip', 'artpulse'); ?></label>
                <input type="number" id="ap-csv-skip" value="0" min="0" style="width:60px;" />
            </p>
            <div id="ap-mapping-step" style="margin-top:20px;"></div>
            <button id="ap-start-import" class="button button-primary" disabled><?php esc_html_e('Start Import', 'artpulse'); ?></button>
            <pre id="ap-import-status"></pre>
            <hr>
            <h2><?php esc_html_e('Export Posts', 'artpulse'); ?></h2>
            <p>
                <a href="<?php echo esc_url(add_query_arg('ap_export_posts', 'artpulse_org')); ?>" class="button"><?php esc_html_e('Export Organizations', 'artpulse'); ?></a>
                <a href="<?php echo esc_url(add_query_arg('ap_export_posts', 'artpulse_event')); ?>" class="button"><?php esc_html_e('Export Events', 'artpulse'); ?></a>
                <a href="<?php echo esc_url(add_query_arg('ap_export_posts', 'artpulse_artist')); ?>" class="button"><?php esc_html_e('Export Artists', 'artpulse'); ?></a>
                <a href="<?php echo esc_url(add_query_arg('ap_export_posts', 'artpulse_artwork')); ?>" class="button"><?php esc_html_e('Export Artworks', 'artpulse'); ?></a>
            </p>
        </div>
        <?php
    }
}
