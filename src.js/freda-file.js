

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

    /**
     * Syncronize File with server (so write and read it again)
     *
     * @returns {Promise<void>}
     */
    async sync() {
        await freda(this.alias).writeFile(this);
        if (this.data !== null)
            this.data = (await freda(this.alias).getFile(this.filename)).data
        else
            this.text = (await freda(this.alias).getFile(this.filename, true)).text
    }

    async delete() {
        await freda(this.alias).deleteFile(this.filename);
    }
}