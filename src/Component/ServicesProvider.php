<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Component/ServicesProvider.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Component;

use MattFerris\Provider\ProviderInterface;

class ServicesProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function provides($consumer)
    {
        $config = $consumer->get('Config');
        $cacheDir = $config->get('app.cacheDir');
        $cacheProvider = $config->get('app.cacheProvider');
        $cache = $consumer->injectConstructor($cacheProvider, ['cacheDir' => $cacheDir]);
        $consumer->set('FormCache', $cache);
    }
}
