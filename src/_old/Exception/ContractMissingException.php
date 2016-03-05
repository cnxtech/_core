<?php
/**
 * Part of the Sebwite PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Codex\Core\Exception;


class ContractMissingException extends CodexException
{
    public function missingContract($contract)
    {
        if ( !is_string($contract) ){
            $contract = get_class($contract);
        }

        $this->setMessage("Class [{$this->class}] should implement interface [{$contract}]");
        return $this;
    }
}