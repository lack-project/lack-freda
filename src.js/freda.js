

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
     * @param asText {boolean}          Return content as text (in text)
     * @returns {Promise<FredaFile>}
     */
    async getFile(filename, asText=false) {
        filename = this.toArrayFilename(filename);

        let ret = await FredaConfig.caller("GET", "/data/{alias}/{filename}", {alias: this.alias, filename: filename, asText: asText});
        return Object.assign(new FredaFile(), ret);
    }

    /**
     *
     * @param filenames {string[]}
     * @returns {Promise<FredaFile[]>}
     */
    async getFiles(filenames, asText = false) {
        let ret = await FredaConfig.caller("POST", "/data", {}, {alias: this.alias, filenames: filenames, asText: asText});

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
     * @param asText {boolean}      Return as text
     * @returns {Promise<FredaFile[]>}
     */
    async getFilesGlob(pattern, asText = false) {
        let ret = await FredaConfig.caller("POST", "/data", {}, {alias: this.alias, globPattern: pattern, asText: asText});

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
        return await FredaConfig.caller("POST", "/data/{alias}/{filename}", {alias: this.alias, filename: file.filename.split("/")}, file);
    }

    /**
     *
     * @param file {FredaFile}
     * @param recursive {boolean}
     * @returns {Promise<any>}
     */
    async deleteFile(file, recursive = false) {
        let filename = file;
        if (file instanceof FredaFile)
            filename = file.filename;

        filename = this.toArrayFilename(filename);
        return FredaConfig.caller("DELETE", "/data/{alias}/{filename}", {alias: this.alias, filename: filename, recursive: recursive});
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
        srcPath = this.toArrayFilename(srcPath);
        destPath = this.toArrayFilename(destPath);
        let ret = await FredaConfig.caller("POST", "/action/copy", {}, {
            srcAlias: srcAlias, destAlias: destAlias, srcPath: srcPath.join("/"), destPath: destPath.join("/"), allowOverwrite: allowOverwrite
        });
    }

    async format (input, format="json_pretty") {
        return (await FredaConfig.caller("POST", "/format", {format: format}, {
            input: input
        })).data;
    }
}
