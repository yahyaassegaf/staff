const fs = require('fs');
const path = require('path');

const srcDir = path.join(__dirname, 'src');

// Safer robust approach: Use parsing to find "console.log(" and count parentheses
function removeConsoleLogSafely(content) {
    let result = content;
    let idx = result.indexOf('console.log');
    
    while (idx !== -1) {
        // Check if it's actually console.log( and not something else
        let openParenIdx = result.indexOf('(', idx);
        
        // If there's no parenthesis, or it's far away, skip it
        let textBetween = result.substring(idx + 11, openParenIdx);
        if (openParenIdx === -1 || textBetween.trim() !== '') {
            idx = result.indexOf('console.log', idx + 11);
            continue;
        }
        
        let parenCount = 1;
        let currIdx = openParenIdx + 1;
        let inString = false;
        let stringChar = '';
        
        while (parenCount > 0 && currIdx < result.length) {
            let char = result[currIdx];
            
            if (inString) {
                if (char === stringChar && result[currIdx - 1] !== '\\') {
                    inString = false;
                }
            } else {
                if (char === '"' || char === "'" || char === '`') {
                    inString = true;
                    stringChar = char;
                } else if (char === '(') {
                    parenCount++;
                } else if (char === ')') {
                    parenCount--;
                }
            }
            currIdx++;
        }
        
        if (parenCount === 0) {
            let endIdx = currIdx;
            // Eat trailing whitespace and semicolon if exists
            while(result[endIdx] === ' ' || result[endIdx] === '\t') endIdx++;
            if (result[endIdx] === ';') endIdx++;
            
            // Eat preceding whitespace if it's the only thing on the line
            let startIdx = idx;
            let lineStart = startIdx - 1;
            let onlySpaces = true;
            while(lineStart >= 0 && result[lineStart] !== '\n') {
                if (result[lineStart] !== ' ' && result[lineStart] !== '\t') {
                    onlySpaces = false;
                    break;
                }
                lineStart--;
            }
            
            if (onlySpaces) {
                startIdx = lineStart + 1;
                // Eat trailing newline if the line is now empty
                if (result[endIdx] === '\n') endIdx++;
                else if (result[endIdx] === '\r' && result[endIdx+1] === '\n') endIdx += 2;
            }
            
            // Remove the matched console.log
            result = result.substring(0, startIdx) + result.substring(endIdx);
            
            // Start looking for the next one from the same index (since we shortened the string)
            idx = result.indexOf('console.log', startIdx);
        } else {
            // Unbalanced or reached end, skip
            idx = result.indexOf('console.log', idx + 11);
        }
    }
    
    return result;
}

function processDirectory(dir) {
    const files = fs.readdirSync(dir);
    
    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        
        if (stat.isDirectory()) {
            processDirectory(fullPath);
        } else if (/\.(vue|js|ts|jsx|tsx)$/.test(fullPath)) {
            const content = fs.readFileSync(fullPath, 'utf8');
            if (content.includes('console.log')) {
                const newContent = removeConsoleLogSafely(content);
                if (newContent !== content) {
                    fs.writeFileSync(fullPath, newContent, 'utf8');
                    console.log(`Cleaned: ${fullPath.replace(__dirname, '')}`);
                }
            }
        }
    }
}

console.log('Starting console.log removal...');
processDirectory(srcDir);
console.log('Done!');
