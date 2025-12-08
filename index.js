const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');
const { MongoClient, ObjectId } = require('mongodb');
const PROTO_PATH = __dirname + '/product.proto';
const packageDefinition = protoLoader.loadSync(PROTO_PATH, { keepCase: false, longs: String, enums: String, defaults: true, oneofs: true });
const proto = grpc.loadPackageDefinition(packageDefinition).marketplace;

const MONGO_URI = process.env.MONGO_URI || "mongodb://monguito:27017";
const DB_NAME = process.env.MONGO_DB || "marketplace_db";
let db;

async function createProduct(call, callback) {
    try {
        const p = call.request;
        const doc = {
            name: p.name,
            description: p.description || "",
            price: p.price || 0.0,
            stock: p.stock || 0,
            category: p.category || "",
            images: p.images || [],
            creationDate: new Date()
        };
        const res = await db.collection('products').insertOne(doc);
        const product = {
            id: res.insertedId.toString(),
            ...doc,
            creationDate: { seconds: Math.floor(doc.creationDate.getTime() / 1000), nanos: 0 }
        };
        callback(null, { ok: true, message: "Created", product });
    } catch (err) {
        callback(null, { ok: false, message: err.message });
    }
}

async function getProduct(call, callback) {
    try {
        const id = call.request.id;
        const doc = await db.collection('products').findOne({ _id: new ObjectId(id) });
        if (!doc) return callback({ code: grpc.status.NOT_FOUND, message: 'Not found' });
        const product = {
            id: doc._id.toString(),
            name: doc.name,
            description: doc.description || "",
            price: doc.price || 0.0,
            stock: doc.stock || 0,
            category: doc.category || "",
            images: doc.images || [],
            creationDate: { seconds: Math.floor(new Date(doc.creationDate).getTime() / 1000), nanos: 0 }
        };
        callback(null, product);
    } catch (err) {
        callback({ code: grpc.status.INTERNAL, message: err.message });
    }
}

async function updateProduct(call, callback) {
    try {
        const p = call.request;
        const id = p.id;
        if (!id) return callback(null, { ok: false, message: "id required" });
        const update = {
            $set: {
                name: p.name,
                description: p.description || "",
                price: p.price || 0.0,
                stock: p.stock || 0,
                category: p.category || "",
                images: p.images || []
            }
        };
        const res = await db.collection('products').findOneAndUpdate({ _id: new ObjectId(id) }, update, { returnDocument: 'after' });
        if (!res.value) return callback(null, { ok: false, message: "Not found" });
        const doc = res.value;
        const product = {
            id: doc._id.toString(),
            name: doc.name, description: doc.description, price: doc.price, stock: doc.stock,
            category: doc.category, images: doc.images,
            creationDate: { seconds: Math.floor(new Date(doc.creationDate).getTime() / 1000), nanos: 0 }
        };
        callback(null, { ok: true, message: "Updated", product });
    } catch (err) {
        callback(null, { ok: false, message: err.message });
    }
}

async function deleteProduct(call, callback) {
    try {
        const id = call.request.id;
        const res = await db.collection('products').deleteOne({ _id: new ObjectId(id) });
        if (res.deletedCount === 0) return callback(null, { ok: false, message: "Not found" });
        callback(null, { ok: true, message: "Deleted" });
    } catch (err) {
        callback(null, { ok: false, message: err.message });
    }
}

async function listProducts(call, callback) {
    try {
        const page = Math.max(call.request.page || 1, 1);
        const limit = Math.max(call.request.limit || 20, 1);
        const skip = (page - 1) * limit;
        const cursor = db.collection('products').find().skip(skip).limit(limit);
        const docs = await cursor.toArray();
        const total = await db.collection('products').countDocuments();
        const data = docs.map(doc => ({
            id: doc._id.toString(),
            name: doc.name,
            description: doc.description || "",
            price: doc.price || 0.0,
            stock: doc.stock || 0,
            category: doc.category || "",
            images: doc.images || [],
            creationDate: { seconds: Math.floor(new Date(doc.creationDate).getTime() / 1000), nanos: 0 }
        }));
        callback(null, { page, limit, total, data });
    } catch (err) {
        callback({ code: grpc.status.INTERNAL, message: err.message });
    }
}

async function main() {
    const client = new MongoClient(MONGO_URI);
    await client.connect();
    db = client.db(DB_NAME);
    const server = new grpc.Server();
    server.addService(proto.ProductService.service, {
        CreateProduct: createProduct,
        GetProduct: getProduct,
        UpdateProduct: updateProduct,
        DeleteProduct: deleteProduct,
        ListProducts: listProducts
    });
    const port = process.env.GRPC_PORT || '50051';
    server.bindAsync(`0.0.0.0:${port}`, grpc.ServerCredentials.createInsecure(), () => {
        server.start();
        console.log(`gRPC server started on ${port}`);
    });
}

main().catch(err => { console.error(err); process.exit(1); });