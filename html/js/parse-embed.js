function parseAudioEmbed(input) {
    strippedInput = input.replace(/\?si=.*$/, ''); // Remove the gross tracking bit Spotify adds
    if (/\.spotify\.com/.test(strippedInput)) {
        var testRegExp = RegExp('(?<=/track/).*$', 'g');
        embedIDTest = testRegExp.exec(strippedInput);
        if (embedIDTest != null) {
            embedID = embedIDTest[0];
            if (embedID.length == 22) {
                embedIDValid = true;
                return embedID;
            } else {
                embedIDValid = false;
                return false;
            }
        } else {
            return false;
        }
    } else if (/spotify:track:/.test(strippedInput)) {
        var testRegExp = RegExp('(?<=:track:).*$', 'g');
        embedIDTest = testRegExp.exec(strippedInput);
        if (embedIDTest != null) {
            embedID = embedIDTest[0];
            if (embedID.length == 22) {
                embedIDValid = true;
                return embedID;
            } else {
                embedIDValid = false;
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

