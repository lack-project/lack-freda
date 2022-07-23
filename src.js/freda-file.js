

class FredaFile {

    isDir() { return false; }
    ifFile() { return true; }

    constructor(filename=null, data=null) {
        this.filename = filename;
        this.data = data;
        this.type;
        this.size;
    }
}