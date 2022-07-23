

class FredaFile {
    /**
     * @type string
     */
    alias;
    /**
     * @type string
     */
    filename;
    /**
     * @type {*}
     */
    data;

    size;


    isDir() { return false; }
    ifFile() { return true; }

    constructor(filename=null, data=null) {
        this.filename = filename;
        this.data = data;
    }

    save() {
        freda(this.alias).writeFile(this);
    }
}