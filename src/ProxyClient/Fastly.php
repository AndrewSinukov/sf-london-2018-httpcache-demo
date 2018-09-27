<?php

/*
 * This file is part of the FOSHttpCache package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ProxyClient;

use FOS\HttpCache\ProxyClient\HttpProxyClient;
use FOS\HttpCache\ProxyClient\Invalidation\PurgeCapable;
use FOS\HttpCache\ProxyClient\Invalidation\TagCapable;

/**
 * Fastly FOSHttpCache Proxy Client for HTTP cache invalidator.
 *
 * VERY SIMPLE EXAMPLE on how the code can be done, not had the opportunity to test this on Fastly itself yet.
 *
 * EDIT: Note that there is a ongoing effort to add official Fastly support here:
 * @see https://github.com/FriendsOfSymfony/FOSHttpCache/pull/403/
 *
 * Additional constructor options we would need to add to this:
 * - service_id   Service ID for the instance you work against on Fastly.
 * - api_token    API token to get access to purge content.
 * - soft_purge   Boolean if we want to use soft purge or not.
 *
 *
 * @see FASTLY API: https://docs.fastly.com/api/purge
 * @see FOS Interfaces: https://github.com/FriendsOfSymfony/FOSHttpCache/tree/master/src/ProxyClient/Invalidation
 * @see Varnish as an example: https://github.com/FriendsOfSymfony/FOSHttpCache/blob/master/src/ProxyClient/Varnish.php
 *
 * @todo Find a way to implement RefreshCapable, or report feature request for that to Fastly (along with multi soft purge)
 *
 * @author André Rømcke <andre.romcke@something.com>
 *
 */
class Fastly extends HttpProxyClient implements PurgeCapable, TagCapable
{
    public const TAG_HEADER = 'Surrogate-Key';

    private const API_TOKEN_HEADER = 'Fastly-Key';
    private const API_SOFT_PURGE_HEADER = 'Fastly-Soft-Purge';

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
        if ($this->options['soft_purge']){
            $this->expireTags($tags);
        } else {
            $this->purgeTags($tags);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($url, array $headers = [])
    {
        if ($this->options['soft_purge']){
            $headers += [self::API_SOFT_PURGE_HEADER => '1'];
        }

        $this->queueRequest('PURGE', $url, $headers);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions()
    {
        $resolver = parent::configureOptions();
        $resolver->setDefaults([
            'service_id' => 'YOUR_FASTLY_SERVICE_ID',
            'api_token' => 'YOUR_FASTLY_TOKEN',
            'soft_purge' => false,
        ]);

        return $resolver;
    }

    /**
     * Purge cache by tags (hard purge).
     *
     * @see https://docs.fastly.com/api/purge#purge_db35b293f8a724717fcf25628d713583
     *
     * @param array $tags
     */
    private function purgeTags(array $tags)
    {
        foreach (array_chunk($tags, 256) as $tagchunk) {
            $this->queueRequest(
                'POST',
                'https://api.fastly.com/service/'. $this->options['service_id'] . '/purge',
                [
                    self::API_TOKEN_HEADER => $this->options['api_token'],
                    self::TAG_HEADER => implode(' ', $tagchunk),
                    'Accept' => 'application/json',
                ],
                false
            );
        }
    }

    /**
     * Expire cache by tags (soft purge).
     *
     * @see https://docs.fastly.com/api/purge#soft_purge_2e4d29085640127739f8467f27a5b549
     *
     * @param array $tags
     */
    private function expireTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->queueRequest(
                'POST',
                'https://api.fastly.com/service/'. $this->options['service_id'] . '/purge/key/' . $tag,
                [
                    self::API_TOKEN_HEADER => $this->options['api_token'],
                    self::API_SOFT_PURGE_HEADER => '1',
                    'Accept' => 'application/json',
                ],
                false
            );
        }
    }
}
