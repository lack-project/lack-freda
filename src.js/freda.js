

const FredaConfig = {
    mount: "%MOUNT%",
    defaultAlias: "default",
    caller: async (method, url, params= {}, body=null) => {
        url = FredaConfig.mount + `${url}`;

        url = url.replace(/{([a-zA-Z0-9_\-]+)}/g, (match, p1) => {
            let val = null;
            if (typeof params[p1] !== "undefined")
                val = params[p1];
            else
                throw "Route param: " + p1 + " not resolvable"
            delete params[p1];
            // Allow Array paths
            if (Array.isArray(val)) {
                return val.map(i => encodeURIComponent(i)).join("/");
            }
            return encodeURIComponent(val);
        })
        if (params !== null) {
            url += "?" + (new URLSearchParams(params));
        }
        let response = await fetch(url, {
            method: method,
            body: body !== null ? JSON.stringify(body) : null,
            headers: {
                "Content-Type": "application/json"
            }
        });
        if ( ! response.ok) {

            let text = await response.text();
            try {
                let json = JSON.parse(text);
                alert ("Request failed: " + response.statusText + "\n\n" + json.error.message);
            } catch (e) {
                alert("Request failed: " + response.statusText + ":\n\n " + text);
                throw e;
            }
        }

        return response.json();
    },
    instance: null,
}

/**
 * Get Instance of Freda
 *
 * @param alias
 * @returns {Freda}
 */
function freda(alias = "default") {
    return new Freda(alias);
}


class Freda {

    constructor(alias = "default") {
        this.alias = alias;
    }

    /**
     *
     * @param filename {string}
     * @returns string[]
     */
    toArrayFilename(filename) {
        if (Array.isArray(filename))
            filename = filename.join("/");

        if (filename.startsWith("/"))
            filename = filename.substring(1);

        return filename.split("/");
    }

    /**
     *
     * @param filename {string|string[]}
     * @returns {Promise<FredaFile>}
     */
    async getFile(filename) {
        filename = this.toArrayFilename(filename);

        let ret = await FredaConfig.caller("GET", "/data/{alias}/{filename}", {alias: this.alias, filename: filename});
        return Object.assign(new FredaFile(), ret);
    }

    /**
     *
     * @param filenames {string[]}
     * @returns {Promise<FredaFile[]>}
     */
    async getFiles(filenames) {
        let ret = await FredaConfig.caller("POST", "/data", {}, {alias: this.alias, filenames: filenames});

        for (let idx in ret) {
            ret[idx] = Object.assign(new FredaFile(), ret[idx]);
        }
        return ret;
    }

    /**
     *
     * <examples>
     * await freda().getFilesGlob("**\/*.json");
     * </examples>
     *
     * @param pattern {string}
     * @returns {Promise<FredaFile[]>}
     */
    async getFilesGlob(pattern) {
        let ret = await FredaConfig.caller("POST", "/data", {}, {alias: this.alias, globPattern: pattern});

        for (let idx in ret) {
            ret[idx] = Object.assign(new FredaFile(), ret[idx]);
        }
        return ret;
    }


    /**
     *
     * @param file {FredaFile}
     * @returns {Promise<any>}
     */
    async writeFile(file) {
        if ( ! file instanceof FredaFile)
            throw new Error("Invalid argument. File needs to be FredaFile");
        return FredaConfig.caller("POST", "/data/{alias}/{filename}", {alias: this.alias, filename: file.filename.split("/")}, file);
    }

    /**
     * Return the url to the raw url of a filename
     *
     * @param filename {string}
     * @returns {string}
     */
    getRawUrl(filename) {
        filename = this.toArrayFilename(filename);
        return FredaConfig.mount + "/raw/" + this.alias + "/" + filename.join("/");
    }


    /**
     *
     * @param dirname {string}
     * @param recursive {boolean}
     * @returns {Promise<FredaTree>}
     */
    async listTree(dirname = "/", recursive = false) {
        dirname = this.toArrayFilename(dirname);

        let ret = await FredaConfig.caller("GET", "/tree/{alias}/{dirname}", {alias: this.alias, dirname: dirname, recursive: recursive});
        return Object.assign(new FredaTree(), ret);
    }


    /**
     *
     * @param srcPath {string}
     * @param destPath {string}
     * @param srcAlias {string}
     * @param destAlias {string}
     * @param allowOverwrite {boolean}
     * @returns {Promise<void>}
     */
    async actionCopy(srcPath, destPath, srcAlias="default", destAlias="default", allowOverwrite=false) {
        let ret = await FredaConfig.caller("POST", "/action/copy", {}, {
            srcAlias: srcAlias, destAlias: destAlias, srcPath: srcPath, destPath: destPath, allowOverwrite: allowOverwrite
        });
    }

}
