<?php

namespace Drupal\speedrun_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "tweet_stream",
 *   admin_label = @Translation("Tweet Stream")
 * )
 */
class TweetStream extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '            <a class="twitter-timeline"  href="https://twitter.com/hashtag/PokemonGO" data-widget-id="773223625281376259">#PokemonGO Tweets</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>',
    ];
  }

}
