<?php

namespace App\Routes;

use Attribute;

/**
 * Marks a public route the sitemap must not advertise.
 *
 * Only the public index has a sitemap at all, so a private path stays out of it by
 * living on a guarded index instead. This is for the paths that are public without
 * being pages: the machine-readable documents, and the entrances to authentication.
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class ExcludeFromSitemap {}
