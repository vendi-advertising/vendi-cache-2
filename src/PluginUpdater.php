<?php declare(strict_types=1);
namespace Vendi\Cache;

use Assert\Assertion;
use Naneau\SemVer\Compare;
use Naneau\SemVer\Parser;
use Naneau\SemVer\Version\Versionable;
use SGH\Comparable\SortFunctions;

class PluginUpdater extends AbstractMaestroEnabledBase
{
    public function is_update_required()
    {
        $updates = $this->get_all_updates();

        Assertion::isArray($updates);

        if (\count($updates) > 0) {
            foreach ($updates as $update) {
                Assertion::isInstanceOf($update, 'Vendi\Cache\SingleUpdateInterface');

                if (Compare::smallerThan($this->get_current_version(), $update->get_update_version())) {
                    return true;
                }
            }
        }

        return false;
    }

    public function perform_updates()
    {
        $updates = $this->get_all_updates();
        foreach ($updates as $update) {
            if (Compare::smallerThan($this->get_current_version(), $update->get_update_version())) {
                $result = $update->perform_update();
                if (true === $result) {
                    $this
                        ->get_maestro()
                        ->get_secretary()
                        ->set_network_option(
                                                'VENDI_CACHE_V2_VERSION',
                                                $update->get_update_version()
                                            )
                    ;
                }
            }
        }
    }

    public function get_all_updates()
    {
        $ret = [];

        SortFunctions::sort($ret);

        return $ret;
    }

    public function get_first_ever_version()
    {
        return Parser::parse('2.0.0');
    }

    public function get_current_version()
    {
        $value = $this
                    ->get_maestro()
                    ->get_secretary()
                    ->get_function_value('get_site_option', 'VENDI_CACHE_V2_VERSION')
                ;

        if (! $value) {
            $value = $this->get_first_ever_version();
        }

        if ($value instanceof Versionable) {
            return $value;
        }

        return Parser::parse($value);
    }
}
