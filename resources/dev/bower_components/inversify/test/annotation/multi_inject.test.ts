declare function __decorate(decorators: ClassDecorator[], target: any, key?: any, desc?: any): void;
declare function __param(paramIndex: number, decorator: ParameterDecorator): ClassDecorator;

///<reference path="../../src/interfaces/interfaces.d.ts" />

import { expect } from "chai";
import { decorate } from "../../src/annotation/decorator_utils";
import multiInject from "../../src/annotation/multi_inject";
import * as METADATA_KEY from "../../src/constants/metadata_keys";
import * as ERROR_MSGS from "../../src/constants/error_msgs";

interface IWeapon {}
class Katana implements IWeapon {}
class Shuriken implements IWeapon {}

class WarriorWithoutDecorator {

    private _primaryWeapon: IWeapon;
    private _secondaryWeapon: IWeapon;

    constructor(
      weapons: IWeapon[]
    ) {

          this._primaryWeapon = weapons[0];
          this._secondaryWeapon = weapons[1];
    }
}

class DecoratedWarrior {

    private _primaryWeapon: IWeapon;
    private _secondaryWeapon: IWeapon;

    constructor(
      @multiInject("IWeapon") weapons: IWeapon[]
    ) {

        this._primaryWeapon = weapons[0];
        this._secondaryWeapon = weapons[1];
    }
}

class InvalidDecoratorUsageWarrior {

    private _primaryWeapon: IWeapon;
    private _secondaryWeapon: IWeapon;

    constructor(
      weapons: IWeapon[]
    ) {
          this._primaryWeapon = weapons[0];
          this._secondaryWeapon = weapons[1];
    }

    public test(a: string) { /*...*/ }
}

describe("@multiInject", () => {

  it("Should generate metadata for named parameters", () => {
    let metadataKey = METADATA_KEY.TAGGED;
    let paramsMetadata = Reflect.getMetadata(metadataKey, DecoratedWarrior);
    expect(paramsMetadata).to.be.an("object");

    // assert metadata for first argument
    expect(paramsMetadata["0"]).to.be.instanceof(Array);
    let m1: IMetadata = paramsMetadata["0"][0];
    expect(m1.key).to.be.eql(METADATA_KEY.MULTI_INJECT_TAG);
    expect(m1.value).to.be.eql("IWeapon");
    expect(paramsMetadata["0"][1]).to.be.undefined;

    // no more metadata should be available
    expect(paramsMetadata["1"]).to.be.undefined;

  });

  it("Should throw when applayed mutiple times", () => {

    let useDecoratorMoreThanOnce = function() {
      __decorate([ __param(0, multiInject("IWeapon")), __param(0, multiInject("IWeapon")) ], InvalidDecoratorUsageWarrior);
    };

    let msg = `${ERROR_MSGS.DUPLICATED_METADATA} ${METADATA_KEY.MULTI_INJECT_TAG}`;
    expect(useDecoratorMoreThanOnce).to.throw(msg);

  });

  it("Should throw when not applayed to a constructor", () => {

    let useDecoratorOnMethodThatIsNotAContructor = function() {
      __decorate([ __param(0, multiInject("IWeapon")) ],
      InvalidDecoratorUsageWarrior.prototype,
      "test", Object.getOwnPropertyDescriptor(InvalidDecoratorUsageWarrior.prototype, "test"));
    };

    let msg = `${ERROR_MSGS.INVALID_DECORATOR_OPERATION}`;
    expect(useDecoratorOnMethodThatIsNotAContructor).to.throw(msg);

  });

  it("Should be usable in VanillaJS applications", () => {

    interface IKatana {}
    interface IShurien {}

    let VanillaJSWarrior = (function () {
        function Warrior(primary: IKatana, secondary: IShurien) {
            // ...
        }
        return Warrior;
    })();

    decorate(multiInject("IWeapon"), VanillaJSWarrior, 0);

    let metadataKey = METADATA_KEY.TAGGED;
    let paramsMetadata = Reflect.getMetadata(metadataKey, VanillaJSWarrior);
    expect(paramsMetadata).to.be.an("object");

    // assert metadata for first argument
    expect(paramsMetadata["0"]).to.be.instanceof(Array);
    let m1: IMetadata = paramsMetadata["0"][0];
    expect(m1.key).to.be.eql(METADATA_KEY.MULTI_INJECT_TAG);
    expect(m1.value).to.be.eql("IWeapon");
    expect(paramsMetadata["0"][1]).to.be.undefined;

    // no more metadata should be available
    expect(paramsMetadata["1"]).to.be.undefined;

  });

});
