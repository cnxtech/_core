///<reference path="../../src/interfaces/interfaces.d.ts" />

import { expect } from "chai";
import Request from "../../src/planning/request";
import Context from "../../src/planning/context";
import Kernel from "../../src/kernel/kernel";
import Target from "../../src/planning/target";
import injectable from "../../src/annotation/injectable";

describe("Request", () => {

  let identifiers = {
      IKatana: "IKatana",
      IKatanaBlade: "IKatanaBlade",
      IKatanaHandler: "IKatanaHandler",
      INinja: "INinja",
      IShuriken: "IShuriken",
  };

  it("Should set its own properties correctly", () => {

      let kernel = new Kernel();
      let context = new Context(kernel);

      let request1 = new Request(
          identifiers.INinja,
          context,
          null,
          null,
          null
      );

      let request2 = new Request(
          identifiers.INinja,
          context,
          null,
          [],
          null
      );

      expect(request1.serviceIdentifier).eql(identifiers.INinja);
      expect(Array.isArray(request1.bindings)).eql(true);
      expect(Array.isArray(request2.bindings)).eql(true);

  });

  it("Should be able to add a child request", () => {

      interface IKatanaBlade {}

      @injectable()
      class KatanaBlade implements IKatanaBlade {}

      interface IKatanaHandler {}

      @injectable()
      class KatanaHandler implements IKatanaHandler {}

      interface IKatana {}

      @injectable()
      class Katana implements IKatana {
          public handler: IKatanaHandler;
          public blade: IKatanaBlade;
          public constructor(handler: IKatanaHandler, blade: IKatanaBlade) {
              // DO NOTHING
          }
      }

      interface IShuriken {}

      @injectable()
      class Shuriken implements IShuriken {}

      interface INinja {}

      @injectable()
      class Ninja implements INinja {
          public katana: IKatana;
          public shuriken: IShuriken;
          public constructor(katana: IKatana, shuriken: IShuriken) {
              // DO NOTHING
          }
      }

      let kernel = new Kernel();
      let context = new Context(kernel);

      let ninjaRequest = new Request(
          identifiers.INinja,
          context,
          null,
          null,
          null
      );

      ninjaRequest.addChildRequest(
          identifiers.IKatana,
          null,
          new Target("katana", identifiers.IKatana));

      let katanaRequest = ninjaRequest.childRequests[0];

      expect(katanaRequest.serviceIdentifier).eql(identifiers.IKatana);
      expect(katanaRequest.parentRequest.serviceIdentifier).eql(identifiers.INinja);
      expect(katanaRequest.childRequests.length).eql(0);
      expect(katanaRequest.target.name.value()).eql("katana");
      expect(katanaRequest.target.serviceIdentifier).eql(identifiers.IKatana);
  });

});
