<?php
/*
 * Copyright (c) 2014 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_TestRunner.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\TestRunner\Composer;

use Composer\Script\Event;

use Stagehand\TestRunner\DependencyInjection\Compiler\Compiler;

/**
 * @since Class available since Release 4.0.0
 */
class Script
{
    /**
     * @param \Composer\Script\Event $event
     */
    public static function compile(Event $event)
    {
        $compiler = new Compiler();
        $compiler->compile();
    }
}
