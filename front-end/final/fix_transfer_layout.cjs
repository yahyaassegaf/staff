const fs = require('fs');
const file = 'c:/laragon/www/staff/front-end/final/src/components/surat_keterangan_transfer/index.vue';
let content = fs.readFileSync(file, 'utf8');

const target = `<div class="input-group" v-else>
                  <span class="input-group-text" v-if="formatParts.skm.prefix">{{ formatParts.skm.prefix }}</span>
                  
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.prefix">{{ formatParts.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_surat"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.no_surat }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.suffix">{{ formatParts.suffix }}</span>
                  <div v-if="errors?.no_surat" class="invalid-feedback">
                    {{ errors.no_surat[0] }}
                  </div>
                </div>
                </div>`;

const replacement = `<div class="input-group" v-else>
                  <span class="input-group-text" v-if="formatParts.skm.prefix">{{ formatParts.skm.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_surat"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.no_surat }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.skm.suffix">{{ formatParts.skm.suffix }}</span>
                  <div v-if="errors?.no_surat" class="invalid-feedback">
                    {{ errors.no_surat[0] }}
                  </div>
                </div>`;

if (content.includes(target)) {
    content = content.replace(target, replacement);
    fs.writeFileSync(file, content, 'utf8');
    console.log('Fixed nested div in SK Transfer component');
} else {
    console.log('Target not found!');
}
