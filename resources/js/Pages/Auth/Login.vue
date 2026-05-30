<script setup></script>
<template>
    <div class="container-fluid">
        <div class="row align-items-center justify-content-center vh-100">
            <div class="col-lg-4">
                <!-- Alert -->
                <div class="alert alert-success" role="alertSuccess">
                    A simple success alert !
                </div>

                <div class="alert alert-warning" role="alertFails">
                    A simple fails alert !
                </div>
                <!-- /Alert -->

                <div class="border border-1 rounded shadow p-4">
                    <h1 class="text-center text-primary">Sign-In</h1>
                    <div>
                        <form action="/login">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter username..."
                                />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    placeholder="Enter password..."
                                />
                            </div>
                            <div class="mb-3 d-grid">
                                <button
                                    class="btn btn-primary btn-lg text-white"
                                >
                                    Sign In
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
