<?php
get_header(); 
while ( have_posts() ) : the_post(); ?>
  <div class="nectar-portfolio-single-media">
    <?php the_post_thumbnail('full',['class'=>'img-responsive']); ?>
  </div>
  <h1 class="entry-title"><?php the_title(); ?></h1>
  <?php echo \ArtPulse\Frontend\ap_render_favorite_button( get_the_ID(), 'artpulse_org' ); ?>
  <div class="entry-content"><?php the_content(); ?></div>
  <?php
    $address = get_post_meta(get_the_ID(),'ead_org_street_address',true);
    $website = get_post_meta(get_the_ID(),'ead_org_website_url',true);
    if($address||$website): ?>
    <ul class="portfolio-meta">
      <?php if($address): ?>
        <li><strong><?php esc_html_e('Address:','artpulse'); ?></strong> <?php echo esc_html($address); ?></li>
      <?php endif; ?>
      <?php if($website): ?>
        <li><strong><?php esc_html_e('Website:','artpulse'); ?></strong>
          <a href="<?php echo esc_url($website); ?>" target="_blank"><?php echo esc_html($website); ?></a>
        </li>
      <?php endif; ?>
    </ul>
  <?php endif; ?>

  <?php
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    $hours = [];
    foreach($days as $day){
      $start  = get_post_meta(get_the_ID(),"ead_org_{$day}_start_time",true);
      $end    = get_post_meta(get_the_ID(),"ead_org_{$day}_end_time",true);
      $closed = get_post_meta(get_the_ID(),"ead_org_{$day}_closed",true);
      if($start || $end || $closed){
        $hours[$day] = [
          'start'  => $start,
          'end'    => $end,
          'closed' => $closed,
        ];
      }
    }
    if(!empty($hours)):
  ?>
    <h2 class="ap-card__title"><?php esc_html_e('Opening Hours','artpulse'); ?></h2>
    <ul class="portfolio-meta opening-hours">
      <?php foreach($hours as $day=>$vals): ?>
        <li><strong><?php echo esc_html(ucfirst($day).':'); ?></strong>
          <?php echo $vals['closed'] ? esc_html__('Closed','artpulse') : esc_html(trim($vals['start'].' - '.$vals['end'])); ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <?php echo \ArtPulse\Frontend\ap_share_buttons( get_permalink(), get_the_title(), get_post_type(), get_the_ID() ); ?>
<?php endwhile; ?>
<?php get_footer(); ?>
