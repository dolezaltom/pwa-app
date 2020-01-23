db.collection('recipes').onSnapshot(snapshot => {
    //console.log(snapshot.docChanges());
    snapshot.docChanges().forEach(change => {
        if (change.type === 'added') {
            renderRecipe(change.doc.data(), change.doc.id);
        }
        if (change.type === 'removed') {
            
        }
    });
});