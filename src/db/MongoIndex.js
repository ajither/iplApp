db.getCollection('Mailbox').createIndex({
    "subject": "text",
    "textPlain": "text",
    "textHtml": "text"
},
        {
            name: "contentIndex"
        });
db.getCollection('Call_Log').createIndex({
    "call_keywords": "text",
    "call_note": "text",
    "Status": "text"
},
        {
            name: "contentIndex"
        });