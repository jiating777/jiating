import { JtModule } from './jt.module';

describe('JtModule', () => {
  let jtModule: JtModule;

  beforeEach(() => {
    jtModule = new JtModule();
  });

  it('should create an instance', () => {
    expect(jtModule).toBeTruthy();
  });
});
