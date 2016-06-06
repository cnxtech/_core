///<reference path="../interfaces/interfaces.d.ts" />

import Metadata from "../planning/metadata";
import { tagParameter } from "./decorator_utils";

// Used to add custom metadata which is used to resolve metadata-based contextual bindings.
function tagged(metadataKey: string, metadataValue: any) {
    return function(target: any, targetKey: string, index: number) {
        let metadata = new Metadata(metadataKey, metadataValue);
        return tagParameter(target, targetKey, index, metadata);
    };
}

export default tagged;
