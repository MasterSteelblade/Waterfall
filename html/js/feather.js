const BlockEmbed = Quill.import('blots/block/embed')
var Delta = Quill.import('delta');
let Break = Quill.import('blots/break');
let Embed = Quill.import('blots/embed');

function lineBreakMatcher() {
    var newDelta = new Delta();
    newDelta.insert({'break': ''});
    return newDelta;
  }

class Divider extends BlockEmbed {
    static create (config) {
        const parentNode = super.create()

        return parentNode
    }
}

Divider.blotName = 'divider'
Divider.tagName = 'hr'

const Module = Quill.import('core/module')
const DEFAULT = {
    icon: '<svg class="icon" style="vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"><path class="ql-fill" d="M64 464h896v96H64v-96zM224 96v160h576V96h96v256H128V96h96z m576 832v-160H224v160H128v-256h768v256h-96z"></path></svg>'
}

class DividerToolbar extends Module {
    constructor (quill, options) {
        super(quill, options)
        this.options = Object.assign({}, DEFAULT, this.options)
        this.quill = quill
        this.toolbar = quill.getModule('toolbar')
        this.toolbar.addHandler('divider', this.dividerHandler.bind(this))
        const divider = document.querySelector('.ql-divider')
        divider.innerHTML = this.options.icon
    }

    dividerHandler () {
        const getSelection = this.quill.getSelection() || {}
        let selection = getSelection.index || this.quill.getLength()
        const [leaf] = this.quill.getLeaf(selection - 1)
        if (leaf instanceof Divider) {
            this.quill.insertText(selection, '\n', Quill.sources.USER)
            selection++
        }
        this.quill.insertEmbed(selection, 'divider', this.options, Quill.sources.USER)
        if (getSelection.index === 0) {
            selection++
            this.quill.insertText(selection, '\n', Quill.sources.USER)
        }
    }
}

class SmartBreak extends Break {
    length () {
      return 1
    }
    value () {
      return '\n'
    }
    
    insertInto(parent, ref) {
      Embed.prototype.insertInto.call(this, parent, ref)
    }
  }
  
  SmartBreak.blotName = 'break';
  SmartBreak.tagName = 'BR'
  
  Quill.register(SmartBreak)

Quill.register(Divider)
Quill.register('modules/divider', DividerToolbar)

const toolbarOptions = {
    container: [
        ['bold', 'italic'],
        ['link', 'image'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['divider'],   
    ]
    }

    var quill = new Quill('#feather-editor', {
      theme: 'snow',
      modules: {
          toolbar: toolbarOptions,
          divider: true,
          clipboard: {
            matchers: [
              ['BR', lineBreakMatcher] 
            ]
          },
          keyboard: {
            bindings: {
              linebreak: {
                key: 13,
                shiftKey: true,
                handler: function (range) {
                  let currentLeaf = this.quill.getLeaf(range.index)[0]
                  let nextLeaf = this.quill.getLeaf(range.index + 1)[0]
      
                  this.quill.insertEmbed(range.index, 'break', true, 'user');
      
                  // Insert a second break if:
                  // At the end of the editor, OR next leaf has a different parent (<p>)
                  if (nextLeaf === null || (currentLeaf.parent !== nextLeaf.parent)) {
                    this.quill.insertEmbed(range.index, 'break', true, 'user');
                  }
      
                  // Now that we've inserted a line break, move the cursor forward
                  this.quill.setSelection(range.index + 1, Quill.sources.SILENT);
                }
              }
            }
          }
      }
    });

    var length = quill.getLength()
var text = quill.getText(length - 2, 2)
// Remove extraneous new lines
if (text === '\n\n') {
  quill.deleteText(quill.getLength() - 2, 2)
}