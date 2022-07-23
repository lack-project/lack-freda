

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
     * @param filename
     * @returns {Promise<FredaFile>}
     */
    async getFile(filename) {
        let ret = await FredaConfig.caller("GET", "/data/{alias}/{filename}", {alias: this.alias, filename});
        return Object.assign(new FredaFile(), ret);
    }

    /**
     *
     * @param file {FredaFile}
     * @returns {Promise<any>}
     */
    async writeFile(file) {
        if ( ! file instanceof FredaFile)
            throw new Error("Invalid argument. File needs to be FredaFile");
        return FredaConfig.caller("POST", "/data/{alias}/{filename}", {alias: this.alias, filename: file.fileName}, file);
    }




}
