// This stub satisfies MathJax's Node-only import during browser bundling.
export function createRequire() {
  return () => {
    throw new Error('MathJax attempted to use Node require in the browser bundle.');
  };
}
