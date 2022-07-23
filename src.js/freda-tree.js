

class FredaTree {
    alias;
    fullPath;
    relPath;
    name;

    type;
    /**
     *
     * @type {FredaTree[]}
     */
    children = [];

    isDir() { return this.type === "directory"; }
    ifFile() { return this.type === "file"; }

    constructor() {

    }

}