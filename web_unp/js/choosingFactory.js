const searchParams = new URLSearchParams(window.location.search)
const factoryIdParamName = 'factory'
const factories = [
    {
        id: 1,
        name: 'УНП-300 (Хабаровск)'
    },
    {
        id: 2,
        name: 'Завод Кострома'
    },
    {
        id: 3,
        name: 'УНП-600 (в производстве)'
    },
    {
        id: 4,
        name: 'УНП-300 (Тест)'
    },
]
const factoriesToHTML = () => {
    let str = ''
    factories.forEach(factory => {
        str += `<option value="${factory.id}">${factory.name}</option>`
    })
    return str
}

document.addEventListener('DOMContentLoaded', () => {
    addCss('/unp/CSS/choosingFactory.css')
    const isSetFactory = searchParams.has(factoryIdParamName)
    if (!isSetFactory || !isRightParam(getCurrentFactoryId())) {
        const chooseFactoryBlock = `
        <div class="choosing_factory_wrapper">
            <form onsubmit="chooseFactory()">
                <label for="factory">
                    Выберите завод:
                    <select name="factory" id="choosing_factory_id"  required>
                        ` + factoriesToHTML() + `
                    </select>
                </label>
                                
                <input type="submit" value="Выбрать" class="pretty-input my-button">
                <button type="button" id='choosing_factory_to_main' class="pretty-input my-button red-button">
                    На главную
                </button>
            </form> 
            
        <div>`
        document.body.innerHTML += chooseFactoryBlock
        document.getElementById('choosing_factory_to_main').addEventListener('click', ()=>{
            console.log('here')
            window.location.href = 'http://www.carbon-dv.ru';
        })
    }
})

function getCurrentFactoryId(){
    return searchParams.get(factoryIdParamName)
}
function isRightParam(factory_id){
    return !!factories.find(f => f.id===Number(factory_id))
}
function chooseFactory(){
    const factory_id = document.getElementById('choosing_factory_id').value
    if(isRightParam(factory_id))
        searchParams.set(factoryIdParamName, factory_id)
}
function addCss(fileName) {

    const head = document.head;
    const link = document.createElement("link");

    link.type = "text/css";
    link.rel = "stylesheet";
    link.href = fileName;

    head.appendChild(link);
}


