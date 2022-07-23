






class FredaDir {

    isDir() { return true; }
    ifFile() { return false; }

    constructor() {
        this.dirName;
        this.childreen = []
    }
}


class FredaFile extends FredaDir {

    isDir() { return false; }
    ifFile() { return true; }

    constructor() {
        super();
        this.fileName = filename;
        this.data = data;
        this.type;
        this.size;
    }
}