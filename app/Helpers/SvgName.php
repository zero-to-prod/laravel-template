<?php

namespace App\Helpers;

/**
 * All of the SVG names that match to the SVG files in the resources/svg directory.
 */
enum SvgName: string
{
    case chevron_down = 'chevron-down';
    case chevron_up = 'chevron-up';
    case city = 'city';
    case command_line = 'command-line';
    case desktop = 'desktop';
    case document = 'document';
    case email = 'email';
    case gear = 'gear';
    case google = 'google';
    case home = 'home';
    case key = 'key';
    case location = 'location';
    case logo = 'logo';
    case logout = 'logout';
    case magnifying_glass = 'magnifying-glass';
    case mailbox = 'mailbox';
    case moon = 'moon';
    case sun = 'sun';
    case swatch = 'swatch';
    case user = 'user';
    case x_mark = 'x-mark';
}
