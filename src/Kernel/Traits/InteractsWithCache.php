<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Kernel\Traits;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\ServiceContainer;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Psr16Cache;
use \Psr\Cache\CacheItemPoolInterface;

/**
 * Trait InteractsWithCache.
 *
 * @author overtrue <i@overtrue.me>
 */
trait InteractsWithCache
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * Get cache instance.
     *
     * @return \Psr\SimpleCache\CacheInterface
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getCache()
    {
        if ($this->cache) {
            return $this->cache;
        }

        if (property_exists($this, 'app') && $this->app instanceof ServiceContainer && isset($this->app['cache'])) {
            $this->setCache($this->app['cache']);
        }

        return $this->cache = $this->createDefaultCache();
    }

    /**
     * Set cache instance.
     *
     * @param \Psr\SimpleCache\CacheInterface|\Psr\Cache\CacheItemPoolInterface $cache
     *
     * @return $this
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function setCache($cache)
    {
        if (empty(\array_intersect([SimpleCacheInterface::class, CacheItemPoolInterface::class], \class_implements($cache)))) {
            throw new InvalidArgumentException(
                \sprintf('The cache instance must implements %s or %s interface.',
                    SimpleCacheInterface::class, CacheItemPoolInterface::class
                )
            );
        }

        if ($cache instanceof CacheItemPoolInterface) {
            $cache = new Psr16Cache($cache);
        }

        $this->cache = $cache;

        return $this;
    }

    /**
     * @return \Psr\SimpleCache\CacheInterface
     */
    protected function createDefaultCache()
    {
        return new Psr16Cache(AbstractAdapter::createSystemCache('easywechat', 2700, '1.0', \sys_get_temp_dir()));
    }
}
