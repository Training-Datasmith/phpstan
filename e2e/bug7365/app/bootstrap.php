<?php

spl_autoload_register(function ($class): void {
	// fake classes which would usually be defined in the clxMobileNet-App itself
	if ('ApplicationController' === $class) {
		class ApplicationController
		{
		}
	}

	if ($class == 'ExternalClass')
	{
		require_once  __DIR__.'/../autoloaded-classes/ExternalClass.php';
	}
});
