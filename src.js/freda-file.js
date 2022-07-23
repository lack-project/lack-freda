

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
     * The parsed data
     * 
     * @type {{*}|*[]|null}
     */
    data;

    /**
     * Raw text data (if file cannot be parsed)
     * @type {string|null}
     */
    text = null;
    
    size;


    isDir() { return false; }
    ifFile() { return true; }

    constructor(filename=null, data=null) {
        this.filename = filename;
        this.data = data;
    }

    async save() {
        return freda(this.alias).writeFile(this);
    }
}